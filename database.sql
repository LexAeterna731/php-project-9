CREATE TABLE IF NOT EXISTS urls (
    id serial PRIMARY KEY,
    name varchar(255) NOT NULL UNIQUE,
    created_at timestamp NOT NULL
);
CREATE TABLE IF NOT EXISTS url_checks (
    id serial PRIMARY KEY,
    url_id int REFERENCES urls (id) NOT NULL,
    status_code int,
    h1 varchar(255),
    title varchar(255),
    description text,
    created_at timestamp NOT NULL
);