CREATE TABLE IF NOT EXISTS Publishers
(
    PublisherID   VARCHAR(10) PRIMARY KEY,
    APIDetailURL  TEXT,
    Description   TEXT,
    ImageFileName TEXT,
    ImageURL      TEXT,
    Name          TEXT
);

CREATE TABLE IF NOT EXISTS Volumes
(
    VolumeID        VARCHAR(10) PRIMARY KEY,
    PublisherID     VARCHAR(10),
    APIDetailURL    TEXT,
    Description     TEXT,
    ImageFileName   TEXT,
    ImageURL        TEXT,
    Name            TEXT,
    StartYear       INTEGER,
    VolumeLocalPath TEXT,
    FOREIGN KEY (PublisherID) REFERENCES Publishers (PublisherID)
);

CREATE TABLE IF NOT EXISTS Issues
(
    IssueID        VARCHAR(10) PRIMARY KEY,
    VolumeID       VARCHAR(10),
    APIDetailURL   TEXT,
    Description    TEXT,
    ImageFileName  TEXT,
    ImageURL       TEXT,
    IssueLocalPath TEXT,
    IssueNumber    VARCHAR(10),
    Name           TEXT,
    FOREIGN KEY (VolumeID) REFERENCES Volumes (VolumeID)
);

CREATE TABLE IF NOT EXISTS UserGroups
(
    UserGroupID INTEGER PRIMARY KEY AUTO_INCREMENT,
    Name        VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS Users
(
    UserID         INTEGER PRIMARY KEY AUTO_INCREMENT,
    Name           VARCHAR(20) UNIQUE,
    HashedPassword VARCHAR(255),
    UserGroupID    INT,
    LastLogin      DATETIME,
    APIKey         VARCHAR(128),
    FOREIGN KEY (UserGroupID) REFERENCES UserGroups (UserGroupID)
);

CREATE TABLE IF NOT EXISTS ReadStatus
(
    IssueID     VARCHAR(10),
    UserID      INTEGER,
    IsRead      TINYINT,
    CurrentPage INTEGER,
    FOREIGN KEY (IssueID) REFERENCES Issues (IssueID) ON DELETE CASCADE ,
    FOREIGN KEY (UserID) REFERENCES Users (UserID) ON DELETE CASCADE
);

DROP TRIGGER IF EXISTS CreateReadStatusAfterIssueInsert;
DROP TRIGGER IF EXISTS CreateReadStatusAfterUserInsert;