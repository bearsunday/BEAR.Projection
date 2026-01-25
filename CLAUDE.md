# BEAR.Projection

## Project Notes

### mysqli Direct Dependency

This project intentionally uses `ext-mysqli` directly instead of PDO. This is required for async database operations - PDO does not support async.

### SQL Parameter Binding

Parameters are bound by name matching, not array order. The `:name` placeholders in SQL are extracted in order of appearance, then matched to parameter values by name. This ensures correct binding regardless of the order parameters are passed.
