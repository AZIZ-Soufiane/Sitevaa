-- Database: sitevaa

CREATE DATABASE sitevaa;


-- Table: Client
CREATE TABLE Client (
    Client_Id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20)
);

-- Table: Project_request
CREATE TABLE Project_request (
    id_request INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    budget DECIMAL(10, 2) NOT NULL,
    timeline VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected', 'reconsider pricing') DEFAULT 'pending',
    Client_id INT NOT NULL,
    FOREIGN KEY (Client_id) REFERENCES Client(Client_Id)
);

-- Table: Invoice
CREATE TABLE Invoice (
    id_invoice INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('sent', 'approved', 'rejected') DEFAULT 'sent',
    id_request INT NOT NULL,
    FOREIGN KEY (id_request) REFERENCES Project_request(id_request)
);

-- Table: Project
CREATE TABLE Project (
    id_project INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('not_started', 'in_progress', 'completed', 'blocked', 'delivered', 'archived') DEFAULT 'not_started',
    id_invoice INT NOT NULL,
    FOREIGN KEY (id_invoice) REFERENCES Invoice(id_invoice)
);

-- Table: Team_member
CREATE TABLE Team_member (
    id_member INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(100),
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Table: Task
CREATE TABLE Task (
    id_task INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('todo', 'in_progress', 'review', 'done') DEFAULT 'todo',
    deadline DATE,
    id_project INT NOT NULL,
    id_member INT,
    FOREIGN KEY (id_project) REFERENCES Project(id_project),
    FOREIGN KEY (id_member) REFERENCES Team_member(id_member)
);