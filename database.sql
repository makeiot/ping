CREATE DATABASE mianshi;
USE mianshi;
CREATE TABLE Tests(
	TestKey varchar(255),
	Package varchar(256),
	Lost	boolean,
	TransTime varChar(255)
);
CREATE TABLE Tests_SUCCESSFUL (
	TestKey varchar(255),
	Package varchar(256),
	Lost	boolean,
	TransTime varChar(255)
);
CREATE TABLE Tests_FAILED (
	TestKey varchar(255),
	Package varchar(256),
	Lost	boolean,
	TransTime varChar(255)
);

