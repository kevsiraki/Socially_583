CREATE TABLE users (
    username varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    password varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    first_name varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,   
    last_name varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
	email VARCHAR(100) NOT NULL UNIQUE KEY,
	id INT NOT NULL AUTO_INCREMENT,
    Unique KEY (id),
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	count INT DEFAULT 0,
	email_verification_link VARCHAR(255) NOT NULL,
	email_verified_at TIMESTAMP,                       
	dob DATE,                                     
	ans VARCHAR(255),                 
	ques INT,                         
	tfaen INT,                         
	tfa VARCHAR(255),
	PRIMARY KEY (username)         
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

