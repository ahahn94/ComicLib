CREATE TRIGGER CreateReadStatusAfterUserInsert
    AFTER INSERT
    ON Users
    FOR EACH ROW
BEGIN
    INSERT INTO ReadStatus (IssueID, UserID, IsRead, CurrentPage) SELECT DISTINCT IssueID, NEW.UserID, 0, 0 FROM Issues;
END