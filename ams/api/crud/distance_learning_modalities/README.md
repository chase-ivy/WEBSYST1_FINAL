# Distance Learning Modalities - CRUD Endpoints

Generic CRUD endpoints for managing distance learning modalities lookup table.

## Seed Data Required

⚠️ **IMPORTANT**: This table is empty by default. You must insert seed data for the enrollment form to function.

See `../../DATABASE_SETUP.md` for SQL INSERT statements.

Quick reference:
```sql
INSERT INTO distance_learning_modalities (name, description, is_active) VALUES
('In-Person Learning', 'Traditional face-to-face classroom instruction', 1),
('Blended Learning', 'Combination of in-person and online instruction', 1),
('Synchronous Online Learning', 'Real-time online instruction (live classes)', 1),
('Asynchronous Online Learning', 'Self-paced online instruction (pre-recorded, materials)', 1),
('Hybrid Learning', 'Mix of synchronous and asynchronous online components', 1),
('Distance Learning', 'Fully remote instruction without in-person meetings', 1),
('Remote Learning', 'Temporary remote learning (emergency protocols)', 1),
('Modular Learning', 'Module-based self-paced learning materials', 1);
```

## Endpoints

### CREATE
**POST** `/api/crud/distance_learning_modalities/c_distance_learning_modalities.php`

**Body:**
```json
{
  "name": "Modality Name",
  "description": "Optional description",
  "is_active": 1
}
```

### READ (List)
**GET** `/api/crud/distance_learning_modalities/r_distance_learning_modalities.php`

Returns all active modalities.

### READ (Single)
**GET** `/api/crud/distance_learning_modalities/r_distance_learning_modalities.php?id=1`

### UPDATE
**POST** `/api/crud/distance_learning_modalities/u_distance_learning_modalities.php`

**Body:**
```json
{
  "id": 1,
  "name": "Updated Name",
  "description": "Updated description",
  "is_active": 1
}
```

### DELETE
**POST** `/api/crud/distance_learning_modalities/d_distance_learning_modalities.php`

**Body:**
```json
{
  "id": 1
}
```

## Usage in Enrollment

The enrollment form's distance learning modalities section fetches available options via:
```javascript
API.distance_learning_modalities.list()
```

If this table is empty, the form will show an empty dropdown.
