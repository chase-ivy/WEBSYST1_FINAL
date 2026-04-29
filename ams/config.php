<?php

class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $host = "localhost:3307";
        $dbname = "gems_db";
        $username = "root";
        $password = "";

        $this->pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password
        );

        $this->pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

// Generic insert method that takes a table name and an array of allowed fields
// This thing is magical, allows us to insert into any table just by passing the right parameters

public function insert(string $table, array $allowedFields): int
{
    $data = [];

    foreach ($allowedFields as $field) {
        // Check if the field exists in $_POST, otherwise set it to null
        $data[$field] = $_POST[$field] ?? null;

        // Validate required fields (e.g., school_year)
        if (is_null($data[$field])) {
            throw new Exception("The field '{$field}' is required and cannot be null.");
        }
    }

    $keys = array_keys($data);
    $escaped_keys = array_map(function($key) { return "`$key`"; }, $keys);
    $placeholders = array_map(function($key) { return ":$key"; }, $keys);

    $sql = "INSERT INTO {$table} (
                " . implode(", ", $escaped_keys) . "
            ) VALUES (
                " . implode(", ", $placeholders) . "
            )";

    $stmt = $this->pdo->prepare($sql);

    foreach ($data as $column => $value) {
        $stmt->bindValue(":" . $column, $value);
    }

    $stmt->execute();

    return (int)$this->pdo->lastInsertId();
}
}


// Initialize the database connection

$db = new Database();

//Students Table

$studentFields = [
    'school_year',
    'grade_level',
    'with_lrn',
    'returning',
    'psa_bcn',
    'lrn',
    'last_name',
    'first_name',
    'middle_name',
    'extension_name',
    'birth_date',
    'sex',
    'place_of_birth',
    'age',
    'mother_tongue',
    'indigenous_group',
    '4p_benificiary',
    'is_learner_with_disability'
];

$studentId = $db->insert('students', $studentFields);

//Current Address

$_POST['student_id'] = $studentId;

$currentAddressFields = [
    'student_id',
    'house_no',
    'street_name',
    'barangay',
    'subdivision_house_no',
    'municipality_city',
    'province',
    'country',
    'zip_code'
];

$db->insert('current_address', $currentAddressFields);

//Permanent Address
// This is like this because if isnt, it will use current address fields aswell.
// Too tired to fix

$_POST['house_no'] = $_POST['permanent_house_no'] ?? null;
$_POST['street_name'] = $_POST['permanent_street_name'] ?? null;
$_POST['barangay'] = $_POST['permanent_barangay'] ?? null;
$_POST['subdivision_house_no'] = $_POST['permanent_subdivision_house_no'] ?? null;
$_POST['municipality_city'] = $_POST['permanent_municipality_city'] ?? null;
$_POST['province'] = $_POST['permanent_province'] ?? null;
$_POST['country'] = $_POST['permanent_country'] ?? null;
$_POST['zip_code'] = $_POST['permanent_zip_code'] ?? null;

$permanentAddressFields = [
    'student_id',
    'house_no',
    'street_name',
    'barangay',
    'subdivision_house_no',
    'municipality_city',
    'province',
    'country',
    'zip_code'
];

$db->insert('permanent_address', $permanentAddressFields);

//Parent / Guardian Information

$parentFields = [
    'student_id',
    'father_last_name',
    'father_first_name',
    'father_middle_name',
    'father_contact_number',
    'mother_last_name',
    'mother_first_name',
    'mother_middle_name',
    'mother_contact_number'
];

$db->insert('parent_guardian_information', $parentFields);

//Returning Learner Information

$returningFields = [
    'student_id',
    'last_grade_level_completed',
    'last_school_attended',
    'last_school_year_completed'
];

$db->insert('returning_learner_information', $returningFields);

//Medical Information

$medicalFields = [
    'student_id',
    'parent_guardian_name',
    'contact_number',
    'exposed_to_cigarette_vape_smoke',
    'other_pertinent_information'
];

$medicalId = $db->insert('medical_information', $medicalFields);


// Medical Allergies
// Same thing as permanent address, we need to set the fields to the right values before inserting,
//  otherwise it will use the previous ones

$_POST['medical_id'] = $medicalId;

$allergyFields = [
    'medical_id',
    'has_allergies',
    'medicine_allergy',
    'pollen_allergy',
    'food_allergy',
    'other_allergy'
];

$db->insert('medical_allergies', $allergyFields);

// Medical Conditions

$conditionFields = [
    'medical_id',
    'has_medical_condition',
    'error_of_refraction',
    'asthma',
    'seizure',
    'heart_illness',
    'anemia',
    'bleeding_disorder',
    'fracture_dislocation',
    'other_condition'
];

$db->insert('medical_conditions', $conditionFields);

// Surgery / Hospitalization

$surgeryFields = [
    'medical_id',
    'has_surgery_hospitalization',
    'surgery_date',
    'hospital_name',
    'body_part'
];

$db->insert('medical_surgery_hospitalization', $surgeryFields);

// Medical Treatment / Medicines

$treatmentFields = [
    'medical_id',
    'is_currently_taking_treatment',
    'treatment_medicine',
    'schedule_dosage'
];

$db->insert('medical_treatment_medicines', $treatmentFields);

// Family Medical History

$familyHistoryFields = [
    'medical_id',
    'has_family_medical_history',
    'tuberculosis',
    'cancer',
    'cancer_type',
    'diabetes_mellitus',
    'hypertension',
    'stroke_heart_attack',
    'depression',
    'kidney_problems',
    'other_condition'
];

$db->insert('family_medical_history', $familyHistoryFields);

echo "All student records saved successfully.";
