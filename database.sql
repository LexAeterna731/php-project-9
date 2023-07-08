CREATE TABLE IF NOT EXISTS urls (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL UNIQUE,
    created_at timestamp NOT NULL
);