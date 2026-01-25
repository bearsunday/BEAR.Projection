CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    total DECIMAL(10,2) NOT NULL
);

INSERT INTO users VALUES ('1', 'John Doe', 'john@example.com');
INSERT INTO orders VALUES ('o1', '1', 100.00), ('o2', '1', 250.50);
