CREATE OR REPLACE VIEW VolumeIssueCount AS
SELECT v.VolumeID, COUNT(DISTINCT i.IssueID) AS IssueCount
FROM Volumes AS v
         LEFT OUTER JOIN Issues as i ON i.VolumeID = v.VolumeID
GROUP BY v.VolumeID;

CREATE OR REPLACE VIEW PublisherVolumes AS
SELECT p.Name AS PublisherName,
       p.PublisherID,
       v.VolumeID,
       v.Name,
       v.ImageFileName,
       v.StartYear,
       vic.IssueCount
FROM Publishers AS p
         JOIN Volumes AS v ON p.PublisherID = v.PublisherID
         LEFT OUTER JOIN VolumeIssueCount AS vic
                         ON vic.VolumeID = v.VolumeID
ORDER BY v.Name;

CREATE OR REPLACE VIEW VolumeIssues AS
SELECT v.volumeID, v.VolumeLocalPath, i.IssueID, i.Name, i.ImageFileName, i.IssueLocalPath, i.IssueNumber
FROM Volumes AS v
         JOIN Issues as i ON v.VolumeID = i.VolumeID;

CREATE or REPLACE VIEW VolumeReadStatus AS
SELECT V.*,
       R.UserID,
       VC.IssueCount,
       IF(VC.IssueCount = SUM(R.IsRead), true, false) AS IsRead,
       MAX(Changed)                                   AS Changed

FROM ReadStatus R
         JOIN Issues I on R.IssueID = I.IssueID
         JOIN Volumes V on I.VolumeID = V.VolumeID
         JOIN VolumeIssueCount VC ON VC.VolumeID = V.VolumeID
GROUP BY V.VolumeID, R.UserID;

CREATE OR REPLACE VIEW IssueReadStatus AS
SELECT I.*, RS.UserID, RS.IsRead, RS.CurrentPage, RS.Changed
FROM Issues I
         JOIN ReadStatus RS on I.IssueID = RS.IssueID;