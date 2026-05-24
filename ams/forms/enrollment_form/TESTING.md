Enrollment Form Verification Checklist

1) Grade Level mapping
- Open the enrollment form and select a `Grade Level` (e.g., "Grade 3").
- Submit a new enrollment via the public form flow.
- Verify the created row in `enrollments` has the `grade_level` value set to the selected string.
- Edit the enrollment in staff/admin UI (teacher/admin Manage Students) and change `Grade Level` there — verify the `enrollments.grade_level` column is updated.

2) Subdivision / House No. persistence
- On the enrollment form, fill in `Subdivision / House No.` under Current and Permanent addresses.
- Submit the enrollment and verify `student_addresses` contains the values in `subdivision_house_no` for the created current/permanent rows.
- If using "Same as Current Address", confirm permanent `subdivision_house_no` is copied from Current.

3) Ownership type normalization
- In the address `Address Status` select choose values like "Rental", "Owned", "Living with Relatives".
- Submit an enrollment or update via admin UI and verify the stored `ownership_type` in `student_addresses` is one of the normalized enum values: `rented`, `owned`, `living_with_relatives`, or `inherited`.

4) Guardian fields
- The enrollment form now only sends basic guardian fields (name, contact). Occupation/facebook/emergency/priority are no longer sent by the public form.
- Submit with guardian data and verify `student_parent_guardians` contains rows with `last_name`, `first_name`, `middle_name`, `contact_number`, and `is_contact_visible`.

5) Quick smoke test
- Create a test student via the public flow (fill required fields).
- Verify the student account is created and an enrollment row is created with non-null `grade_level` and address rows.
- Use admin UI to edit the student and confirm updates persist.

Notes
- PDF export still contains legacy `with_lrn`/`school_id` placeholders intentionally left unchanged for now.
- If you want, I can add simple automated PHPUnit endpoints tests or small curl commands for each verification step; tell me which to add.
