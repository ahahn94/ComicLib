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
    ReadStatus      TINYINT,
    StartYear       INTEGER,
    VolumeLocalPath TEXT,
    FOREIGN KEY (PublisherID) REFERENCES Publishers (PublisherID)
);

CREATE TABLE IF NOT EXISTS Issues
(
    IssueID         VARCHAR(10) PRIMARY KEY,
    VolumeID        VARCHAR(10),
    APIDetailURL    TEXT,
    Description     TEXT,
    ImageFileName   TEXT,
    ImageURL        TEXT,
    IssueLocalPath  TEXT,
    IssueNumber     VARCHAR(10),
    Name            TEXT,
    ReadStatus      TINYINT,
    FOREIGN KEY (VolumeID) REFERENCES Volumes (VolumeID)
);