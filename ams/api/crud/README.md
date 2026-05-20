# CRUD Endpoints

This directory contains table-specific CRUD endpoint entry points.

Each table folder contains:
- `c_<table>.php`
- `r_<table>.php`
- `u_<table>.php`
- `d_<table>.php`

These files all include `crud_base.php`, which implements the shared request handling and validation logic.
