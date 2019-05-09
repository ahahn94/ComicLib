CREATE OR REPLACE VIEW VolumeIssueCount AS
SELECT v.VolumeID, COUNT(*) AS IssueCount
FROM Issues AS i
         JOIN Volumes as v ON i.VolumeID = v.VolumeID
GROUP BY i.VolumeID;

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
         JOIN VolumeIssueCount AS vic
              ON vic.VolumeID = v.VolumeID
ORDER BY v.Name;

CREATE OR REPLACE VIEW VolumeIssues AS
SELECT v.volumeID, v.VolumeLocalPath, i.IssueID, i.IssueLocalPath, i.IssueNumber
FROM Volumes AS v
         JOIN Issues as i ON v.VolumeID = i.VolumeID;
