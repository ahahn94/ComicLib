CREATE TRIGGER CreateReadStatusAfterUserInsert
    AFTER INSERT
    ON Users
    FOR EACH ROW
BEGIN
    INSERT INTO ReadStatus (IssueID, UserID, IsRead, CurrentPage, Changed)
    SELECT DISTINCT IssueID, NEW.UserID, 0, 0, UTC_TIMESTAMP()
    FROM Issues;
END