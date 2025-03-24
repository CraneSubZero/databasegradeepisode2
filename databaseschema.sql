-- Create the database
CREATE DATABASE IF NOT EXISTS grade_calculator;
USE grade_calculator;

-- Create Student table
CREATE TABLE IF NOT EXISTS Student (
    StudentID INT AUTO_INCREMENT PRIMARY KEY,
    LName VARCHAR(50) NOT NULL,
    FName VARCHAR(50) NOT NULL,
    MI CHAR(1)
);

-- Create Faculty table
CREATE TABLE IF NOT EXISTS Faculty (
    FacultyID INT AUTO_INCREMENT PRIMARY KEY,
    LName VARCHAR(50) NOT NULL,
    FName VARCHAR(50) NOT NULL,
    MI CHAR(1)
);

-- Create Course table
CREATE TABLE IF NOT EXISTS Course (
    CourseID INT AUTO_INCREMENT PRIMARY KEY,
    CourseName VARCHAR(100) NOT NULL,
    CourseDescription TEXT
);

-- Create Grades table
CREATE TABLE IF NOT EXISTS Grades (
    GradeID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    CourseID INT NOT NULL,
    FacultyID INT NOT NULL,
    Prelim DECIMAL(5,2),
    Midterm DECIMAL(5,2),
    Final DECIMAL(5,2),
    GWA DECIMAL(5,2),
    EQ DECIMAL(3,2),
    Remarks VARCHAR(20),
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID),
    FOREIGN KEY (FacultyID) REFERENCES Faculty(FacultyID)
);

-- Create detailed grades tables to store component scores
CREATE TABLE IF NOT EXISTS DetailedGrades (
    DetailID INT AUTO_INCREMENT PRIMARY KEY,
    GradeID INT NOT NULL,
    Term ENUM('Prelim', 'Midterm', 'Endterm') NOT NULL,
    Assignment DECIMAL(5,2),
    Quiz DECIMAL(5,2),
    Seatwork DECIMAL(5,2),
    Participation DECIMAL(5,2),
    Projects DECIMAL(5,2),
    LabExercises DECIMAL(5,2),
    WrittenExam DECIMAL(5,2),
    FOREIGN KEY (GradeID) REFERENCES Grades(GradeID)
);

-- Insert sample data
INSERT INTO Student (LName, FName, MI) VALUES 
('Smith', 'John', 'A'),
('Garcia', 'Maria', 'B'),
('Johnson', 'Robert', 'C');

INSERT INTO Faculty (LName, FName, MI) VALUES 
('Brown', 'David', 'J'),
('Davis', 'Sarah', 'L');

INSERT INTO Course (CourseName, CourseDescription) VALUES 
('Programming 101', 'Introduction to programming concepts'),
('Database Systems', 'Study of database management systems');