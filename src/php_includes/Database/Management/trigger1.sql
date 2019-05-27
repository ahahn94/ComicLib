CREATE TRIGGER CreateReadStatusAfterIssueInsert
    AFTER INSERT
    ON Issues
    FOR EACH ROW
BEGIN
    INSERT INTO ReadStatus (IssueID, UserID, IsRead, CurrentPage) SELECT DISTINCT NEW.IssueID, UserID, 0, 0 FROM Users;
END