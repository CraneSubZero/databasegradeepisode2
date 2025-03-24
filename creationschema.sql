-- SQL Insert Script for easier data insertion

-- Additional students
INSERT INTO Student (LName, FName, MI) VALUES 
('Jones', 'Michael', 'T'),
('Wong', 'Jennifer', 'L'),
('Martinez', 'Carlos', 'R'),
('Kim', 'Sophia', 'J'),
('Taylor', 'William', 'E');

-- Additional faculty
INSERT INTO Faculty (LName, FName, MI) VALUES 
('Wilson', 'Elizabeth', 'M'),
('Thompson', 'James', 'P'),
('Lopez', 'Ana', 'C');

-- Additional courses
INSERT INTO Course (CourseName, CourseDescription) VALUES 
('Data Structures', 'Advanced data structures and algorithms'),
('Web Development', 'Front-end and back-end web development'),
('Object-Oriented Programming', 'OOP concepts and practices'),
('Database Management', 'Design and implementation of database systems');

-- Sample SQL to insert grades (for reference)

INSERT INTO Grades (StudentID, CourseID, FacultyID, Prelim, Midterm, Final, GWA, EQ, Remarks)
VALUES 
(1, 1, 1, 85.25, 87.50, 90.75, 88.23, 1.75, 'Passed'),
(2, 2, 2, 78.50, 80.25, 82.75, 80.83, 2.25, 'Passed');

-- Sample detailed grades
INSERT INTO DetailedGrades 
(GradeID, Term, Assignment, Quiz, Seatwork, Participation, Projects, LabExercises, WrittenExam)
VALUES
(1, 'Prelim', 85, 170, 110, 18, 100, 350, 85),
(1, 'Midterm', 88, 175, 115, 19, 105, 360, 87),
(1, 'Endterm', 90, 180, 120, 20, 110, 380, 90);


-- Query to view complete grade information
CREATE VIEW StudentGradeView AS
SELECT 
    CONCAT(s.LName, ', ', s.FName, ' ', s.MI) AS StudentName,
    c.CourseName,
    CONCAT(f.LName, ', ', f.FName, ' ', f.MI) AS FacultyName,
    g.Prelim,
    g.Midterm,
    g.Final,
    g.GWA,
    g.EQ,
    g.Remarks
FROM Grades g
JOIN Student s ON g.StudentID = s.StudentID
JOIN Course c ON g.CourseID = c.CourseID
JOIN Faculty f ON g.FacultyID = f.FacultyID;

-- Query to view detailed component grades
CREATE VIEW DetailedGradeView AS
SELECT 
    CONCAT(s.LName, ', ', s.FName, ' ', s.MI) AS StudentName,
    c.CourseName,
    dg.Term,
    dg.Assignment,
    dg.Quiz,
    dg.Seatwork,
    dg.Participation,
    dg.Projects,
    dg.LabExercises,
    dg.WrittenExam
FROM DetailedGrades dg
JOIN Grades g ON dg.GradeID = g.GradeID
JOIN Student s ON g.StudentID = s.StudentID
JOIN Course c ON g.CourseID = c.CourseID;