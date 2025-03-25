<?php
// Database connection
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password
$dbname = "grade_calculator";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get student, faculty, and course information
    $studentId = isset($_POST['student_id']) ? $_POST['student_id'] : null;
    $facultyId = isset($_POST['faculty_id']) ? $_POST['faculty_id'] : null;
    $courseId = isset($_POST['course_id']) ? $_POST['course_id'] : null;
    
    // Get grade calculations
    $prelimGrade = $_POST['prelim_grade'];
    $midtermGrade = $_POST['midterm_grade'];
    $endtermGrade = $_POST['endterm_grade'];
    $totalGrade = $_POST['total_grade'];
    $equivalentGrade = $_POST['equivalent_grade'];
    $remarks = $_POST['grade_result'];
    
    // Insert into Grades table
    $sql = "INSERT INTO Grades (StudentID, CourseID, FacultyID, Prelim, Midterm, Final, GWA, EQ, Remarks) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiidddids", $studentId, $courseId, $facultyId, $prelimGrade, $midtermGrade, $endtermGrade, $totalGrade, $equivalentGrade, $remarks);
    $stmt->execute();
    $gradeId = $stmt->insert_id;
    
    // Insert detailed grade components for Prelim
    insertDetailedGrade($conn, $gradeId, 'Prelim', 
        $_POST['prelim_assignment'], $_POST['prelim_quiz'], 
        $_POST['prelim_seatwork'], $_POST['prelim_participation'], 
        $_POST['prelim_projects'], $_POST['prelim_lab'], 
        $_POST['prelim_exam']);
    
    // Insert detailed grade components for Midterm
    insertDetailedGrade($conn, $gradeId, 'Midterm', 
        $_POST['midterm_assignment'], $_POST['midterm_quiz'], 
        $_POST['midterm_seatwork'], $_POST['midterm_participation'], 
        $_POST['midterm_projects'], $_POST['midterm_lab'], 
        $_POST['midterm_exam']);
    
    // Insert detailed grade components for Endterm
    insertDetailedGrade($conn, $gradeId, 'Endterm', 
        $_POST['endterm_assignment'], $_POST['endterm_quiz'], 
        $_POST['endterm_seatwork'], $_POST['endterm_participation'], 
        $_POST['endterm_projects'], $_POST['endterm_lab'], 
        $_POST['endterm_exam']);
    
    echo "Grade data saved successfully!";
}

// Function to insert detailed grade components
function insertDetailedGrade($conn, $gradeId, $term, $assignment, $quiz, $seatwork, $participation, $projects, $lab, $exam) {
    // Debug: Print out all parameters to verify
    error_log("Inserting Detailed Grade - GradeID: $gradeId, Term: $term, Assign: $assignment, Quiz: $quiz, Seatwork: $seatwork, Participation: $participation, Projects: $projects, Lab: $lab, Exam: $exam");

    $sql = "INSERT INTO DetailedGrades (GradeID, Term, Assignment, Quiz, Seatwork, Participation, Projects, LabExercises, WrittenExam) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Log prepare error
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    // Ensure all values are numeric and not null
    $assignment = floatval($assignment);
    $quiz = floatval($quiz);
    $seatwork = floatval($seatwork);
    $participation = floatval($participation);
    $projects = floatval($projects);
    $lab = floatval($lab);
    $exam = floatval($exam);

    // Bind parameters with explicit type casting
    $stmt->bind_param("isddddddd", 
        $gradeId, 
        $term, 
        $assignment, 
        $quiz, 
        $seatwork, 
        $participation, 
        $projects, 
        $lab, 
        $exam
    );

    // Execute and check for errors
    $result = $stmt->execute();
    if ($result === false) {
        // Log execution error
        error_log("Execute failed: " . $stmt->error);
        return false;
    }

    return true;
}

// Get all students, courses, and faculty for dropdowns
$students = $conn->query("SELECT StudentID, CONCAT(LName, ', ', FName, ' ', MI) AS FullName FROM Student ORDER BY LName");
$courses = $conn->query("SELECT CourseID, CourseName FROM Course ORDER BY CourseName");
$faculty = $conn->query("SELECT FacultyID, CONCAT(LName, ', ', FName, ' ', MI) AS FullName FROM Faculty ORDER BY LName");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Grade Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: auto;
            padding: 20px;
            background-color: #212529;
            color: #f8f9fa;
            transition: background-color 0.3s, color 0.3s;
        }
        label, input, select {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .dark-mode {
            background-color: #f8f9fa;
            color: #212529;
        }
        .dark-mode input, .dark-mode select {
            background-color: #ffffff;
            color: #212529;
            border: 1px solid #212529;
        }
        .form-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #495057;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <button onclick="toggleDarkMode()">Toggle Dark Mode</button>
    <h2>Semester Grade Calculator</h2>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-section">
            <h3>Student Information</h3>
            <label>Select Student:
                <select name="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php while($row = $students->fetch_assoc()): ?>
                        <option value="<?php echo $row['StudentID']; ?>"><?php echo $row['FullName']; ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            
            <label>Select Course:
                <select name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php while($row = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $row['CourseID']; ?>"><?php echo $row['CourseName']; ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            
            <label>Select Faculty:
                <select name="faculty_id" required>
                    <option value="">-- Select Faculty --</option>
                    <?php while($row = $faculty->fetch_assoc()): ?>
                        <option value="<?php echo $row['FacultyID']; ?>"><?php echo $row['FullName']; ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
        </div>
    
        <h3>Prelim</h3>
        <label>Assignment: (100)<input type="number" id="prelim_assignment" name="prelim_assignment"></label>
        <label>Quiz: (200)<input type="number" id="prelim_quiz" name="prelim_quiz"></label>
        <label>Seatwork: (125)<input type="number" id="prelim_seatwork" name="prelim_seatwork"></label>
        <label>Participation: (20)<input type="number" id="prelim_participation" name="prelim_participation"></label>
        <label>Projects: (115)<input type="number" id="prelim_projects" name="prelim_projects"></label>
        <label>Lab Exercises: (400)<input type="number" id="prelim_lab" name="prelim_lab"></label>
        <label>Written Exam: (100)<input type="number" id="prelim_exam" name="prelim_exam"></label>
        <label>Prelim Grade: <input type="text" id="prelim_grade" name="prelim_grade" readonly></label>
        
        <h3>Midterm</h3>
        <label>Assignment: (100)<input type="number" id="midterm_assignment" name="midterm_assignment"></label>
        <label>Quiz: (200)<input type="number" id="midterm_quiz" name="midterm_quiz"></label>
        <label>Seatwork: (125)<input type="number" id="midterm_seatwork" name="midterm_seatwork"></label>
        <label>Participation: (20)<input type="number" id="midterm_participation" name="midterm_participation"></label>
        <label>Projects: (115)<input type="number" id="midterm_projects" name="midterm_projects"></label>
        <label>Lab Exercises: (400)<input type="number" id="midterm_lab" name="midterm_lab"></label>
        <label>Written Exam: (100)<input type="number" id="midterm_exam" name="midterm_exam"></label>
        <label>Midterm Grade: <input type="text" id="midterm_grade" name="midterm_grade" readonly></label>
        
        <h3>Endterm</h3>
        <label>Assignment: (100)<input type="number" id="endterm_assignment" name="endterm_assignment"></label>
        <label>Quiz: (200)<input type="number" id="endterm_quiz" name="endterm_quiz"></label>
        <label>Seatwork: (125)<input type="number" id="endterm_seatwork" name="endterm_seatwork"></label>
        <label>Participation: (20)<input type="number" id="endterm_participation" name="endterm_participation"></label>
        <label>Projects: (115)<input type="number" id="endterm_projects" name="endterm_projects"></label>
        <label>Lab Exercises: (400)<input type="number" id="endterm_lab" name="endterm_lab"></label>
        <label>Written Exam: (100)<input type="number" id="endterm_exam" name="endterm_exam"></label>
        <label>Endterm Grade: <input type="text" id="endterm_grade" name="endterm_grade" readonly></label>
        
        <button type="button" onclick="calculateGrades()">Calculate Grades</button>
        <h3>Total Grade: <input type="text" id="total_grade" name="total_grade" readonly></h3>
        <h3>Equivalent Grade: <input type="text" id="equivalent_grade" name="equivalent_grade" readonly></h3>
        <h3>Result: <input type="text" id="grade_result" name="grade_result" readonly></h3>
        
        <button type="submit">Save to Database</button>
    </form>
    
    <script>
        function computeGrade(assign, quiz, seatwork, participation, projects, lab, exam) {
            let classStanding = ((assign + quiz + seatwork + participation) / 4) * 0.2;
            let taskPerformance = ((projects + lab) / 2) * 0.3;
            let examScore = (exam / 100) * 0.5;
            return classStanding + taskPerformance + examScore;
        }
        
        function adjustGrade(grade) {
            if (grade >= 73.5) return 75;
            if (grade < 73.5) return 70;
            return grade;
        }
        
        function calculateEquivalentGrade(totalGrade) {
            if (totalGrade >= 97) return 1.0;
            if (totalGrade >= 93) return 1.25;
            if (totalGrade >= 89) return 1.5;
            if (totalGrade >= 85) return 2.0;
            if (totalGrade >= 81) return 2.25;
            if (totalGrade >= 77) return 2.5;
            if (totalGrade >= 69) return 2.75;
            if (totalGrade >= 65) return 3.0;
            if (totalGrade >= 50) return 4.0;
            return 5.0;
        }
        
        function calculateGrades() {
            let prelim = computeGrade(
                parseFloat(document.getElementById('prelim_assignment').value) || 0,
                parseFloat(document.getElementById('prelim_quiz').value) || 0,
                parseFloat(document.getElementById('prelim_seatwork').value) || 0,
                parseFloat(document.getElementById('prelim_participation').value) || 0,
                parseFloat(document.getElementById('prelim_projects').value) || 0,
                parseFloat(document.getElementById('prelim_lab').value) || 0,
                parseFloat(document.getElementById('prelim_exam').value) || 0
            );
            document.getElementById('prelim_grade').value = prelim.toFixed(2);
            
            let midterm = computeGrade(
                parseFloat(document.getElementById('midterm_assignment').value) || 0,
                parseFloat(document.getElementById('midterm_quiz').value) || 0,
                parseFloat(document.getElementById('midterm_seatwork').value) || 0,
                parseFloat(document.getElementById('midterm_participation').value) || 0,
                parseFloat(document.getElementById('midterm_projects').value) || 0,
                parseFloat(document.getElementById('midterm_lab').value) || 0,
                parseFloat(document.getElementById('midterm_exam').value) || 0
            );
            document.getElementById('midterm_grade').value = midterm.toFixed(2);
            
            let endterm = computeGrade(
                parseFloat(document.getElementById('endterm_assignment').value) || 0,
                parseFloat(document.getElementById('endterm_quiz').value) || 0,
                parseFloat(document.getElementById('endterm_seatwork').value) || 0,
                parseFloat(document.getElementById('endterm_participation').value) || 0,
                parseFloat(document.getElementById('endterm_projects').value) || 0,
                parseFloat(document.getElementById('endterm_lab').value) || 0,
                parseFloat(document.getElementById('endterm_exam').value) || 0
            );
            document.getElementById('endterm_grade').value = endterm.toFixed(2);
            
            let totalGrade = (prelim * 0.3) + (midterm * 0.3) + (endterm * 0.4);
            let eqGrade = calculateEquivalentGrade(totalGrade);
            document.getElementById('total_grade').value = totalGrade.toFixed(2);
            document.getElementById('equivalent_grade').value = eqGrade.toFixed(2);
            document.getElementById('grade_result').value = totalGrade >= 75 ? 'Passed' : 'Failed';
        }
        
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }
    </script>
</body>
</html>