# BDRパターン (Business Domain Repository Pattern)

## コアコンセプト

BDRパターンは**SQLとオブジェクト指向が調和して動作する**パラダイムです。一方を他方に模倣させるのではなく、それぞれが得意な領域で力を発揮します。

- **SQLはSQLのまま** - 複雑なクエリ、JOIN、最適化が最大のパフォーマンスで動作
- **オブジェクトはオブジェクトのまま** - ドメインモデルは単なるデータコンテナではなく、振る舞いを持つ自律的なエンティティ

## なぜ「Projection」か？

CQRSの用語で**Projection**とは、データの読み取り最適化されたビュー、つまり基盤となる状態を画面やAPIレスポンスに「投影」したものです。

CQRSの設計者Greg Youngは、Queryモデルは**「使い捨て（Disposable）」**でいいと述べました。Projectionは永続的なメンテナンスを必要とする神聖なドメインモデルではありません。特定の画面のために最適化されたスナップショットです。画面が変わったら、捨てて作り直せばいい。

これにより開発者は以下から解放されます：
- 複雑なORMマッピング
- 遅延読み込みの懸念
- シリアライズとの格闘
- Queryモデルを「アーキテクチャ的に正しく」する重荷

参照: [CQRSとイミュータブルデータモデル](https://qiita.com/koriym/items/4e52758d5327447fed13)

## 3層アーキテクチャ

```
┌─────────────────────────────────────────────────────────┐
│  SQL層                                                  │
│  user_profile.sql                                       │
│  ┌───────────────────────────────────────────────────┐  │
│  │ SELECT id, name, birth_date, avatar_path          │  │
│  │ FROM users WHERE id = :id                         │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│  Factory層 (DIあり)                                     │
│  UserProfileFactory                                     │
│  ┌───────────────────────────────────────────────────┐  │
│  │ - AgeCalculator (注入)                            │  │
│  │ - ImageUrlResolver (注入)                         │  │
│  │                                                   │  │
│  │ 変換: birth_date → age                            │  │
│  │ 変換: avatar_path → 完全なURL                     │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│  ドメインオブジェクト層                                 │
│  UserProfile (イミュータブル)                           │
│  ┌───────────────────────────────────────────────────┐  │
│  │ + id: string                                      │  │
│  │ + name: string                                    │  │
│  │ + age: int                                        │  │
│  │ + avatarUrl: string                               │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### SQL層

最適化されたクエリを別ファイルの`.sql`に記述。ORMの抽象化が実際のデータベース操作を隠蔽することはありません。

```sql
-- var/sql/query/user_profile.sql
SELECT id, name, birth_date, avatar_path
FROM users
WHERE id = :id
```

### Factory層

生のクエリ結果をドメインオブジェクトに変換します。データ変換とバリデーションのために依存性が注入されます。

```php
final class UserProfileFactory
{
    public function __construct(
        private readonly AgeCalculator $ageCalculator,
        private readonly ImageUrlResolver $imageResolver,
    ) {
    }

    public function __invoke(
        string $id,
        string $name,
        string $birthDate,
        string $avatarPath,
    ): UserProfile {
        return new UserProfile(
            id: $id,
            name: $name,
            age: $this->ageCalculator->fromBirthDate($birthDate),
            avatarUrl: $this->imageResolver->resolve($avatarPath),
        );
    }
}
```

### ドメインオブジェクト層

データベースのスナップショットを表すイミュータブルな読み取り専用オブジェクト。セッターなし、`save()`メソッドなし。

```php
final class UserProfile
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $age,
        public readonly string $avatarUrl,
    ) {
    }
}
```

## なぜこのパターンか？

### 関心の明確な分離

各層が単一の責務を持ちます：
- SQL: データ取得の最適化
- Factory: 変換と依存性注入
- ドメインオブジェクト: 型安全な表現

### 独立したテスト

各層が独立してテスト可能：
- SQL層はデータ取得を検証
- Factory層は変換ロジックを確認
- ドメインオブジェクト層は構造を検証

### AIツールに対する透明性

明示的な依存関係、可視化されたSQL、追跡可能なロジックフローは、不透明なORM抽象化と比べてより良い自動化を可能にします。

### 「シンプルなCRUD」という幻想はない

すべてのアプリケーションは最終的に以下を必要とします：
- 表示フォーマット
- 権限チェック
- 関連データの取得
- パフォーマンス最適化

BDRは最初から正しい構造を提供します。
