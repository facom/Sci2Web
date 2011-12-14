drop table if exists Diffusion_dev;
create table Diffusion_dev (
dbrunhash char(32) not null,
dbauthor varchar(255),
dbdate datetime not null,
OutputBasename varchar(100),
PeriodOutput int,
BaseUnits float,
PlotFile varchar(255),
MeanDispersions float,
Height float,
Width float,
NumberParticles int,
MeanFreePath float,
DelayTime float,
QuerySleep tinyint(1),
GeneralComments text,
primary key (dbrunhash),
#LINKS
runs_runcode char(8)
);
