drop table if exists `MercuPy_1.0-4B`;
create table `MercuPy_1.0-4B` (
dbrunhash char(32) not null,
dbauthor varchar(255),
dbname varchar(255),
dbtemplate varchar(255),
dbdate datetime not null,
TimeUnits varchar(255),
Epoch float,
DtOutput float,
CentralReference varchar(255),
TimeRelativeIntegration varchar(255),
SystemDescription text,
Body1Mass varchar(255),
Body1Class varchar(255),
Body1Density float,
Body1Radius float,
Body1Units varchar(255),
Body1State varchar(255),
Body1J2 float,
Body1J4 float,
Body1J6 float,
Body2Type varchar(255),
Body2Mass varchar(255),
Body2Class varchar(255),
Body2Density float,
Body2Radius float,
Body2Reference varchar(255),
Body2Units varchar(255),
Body2State varchar(255),
Body2As varchar(255),
Body2Spin varchar(255),
Body2Close float,
Body3Type varchar(255),
Body3Mass varchar(255),
Body3Class varchar(255),
Body3Density float,
Body3Radius float,
Body3Reference varchar(255),
Body3Units varchar(255),
Body3State varchar(255),
Body3As varchar(255),
Body3Spin varchar(255),
Body3Close float,
Body4Type varchar(255),
Body4Mass varchar(255),
Body4Class varchar(255),
Body4Density float,
Body4Radius float,
Body4Reference varchar(255),
Body4Units varchar(255),
Body4State varchar(255),
Body4As varchar(255),
Body4Spin varchar(255),
Body4Close float,
IntegrationAlgorithm varchar(255),
IntegrationAccuracy float,
IntegrationPrecision varchar(255),
IntegrationChangeOver float,
DStepPeriodic int,
StopIntegration varchar(255),
Collisions varchar(255),
ChangeEnergy float,
ChangeMomentum float,
primary key (dbrunhash),
runs_runcode char(8)
);
