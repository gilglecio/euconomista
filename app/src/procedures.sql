DELIMITER $$

CREATE PROCEDURE  `register_user` (IN `name` VARCHAR(45), IN `email` VARCHAR(60), IN `password` VARCHAR(60))
BEGIN
    set @entity = (select max(entity) from users);
    INSERT INTO users SET entity = @entity + 1, name = name, email = email, password = password, created_at = now(), updated_at = now();
END $$

DELIMITER ;