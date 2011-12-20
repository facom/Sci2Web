drop table if exists `TestDiffusion_dev`;
create table `TestDiffusion_dev` (
dbrunhash char(32) not null,
dbauthor varchar(255),
dbdate datetime not null,
OutputFile varchar(255),
Pausable tinyint(1),
IdleTime int,
CustomUnits float,
PlotStyle text,
MeanDispersions float,
NumberParticles int,
BoxHeight float,
BoxWidth float,
MeanFreePath float,
primary key (dbrunhash),
runs_runcode char(8)
);
