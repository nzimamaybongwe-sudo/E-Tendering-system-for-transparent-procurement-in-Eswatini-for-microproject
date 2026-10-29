USE e_tendering;

ALTER TABLE contractors
ADD COLUMN contact_person VARCHAR(150) DEFAULT NULL AFTER local_ownership;

ALTER TABLE contractors
ADD COLUMN phone VARCHAR(80) DEFAULT NULL AFTER contact_person;

ALTER TABLE contractors
ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER phone;
