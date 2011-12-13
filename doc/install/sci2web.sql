#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%#
#          _ ___               _						 #
#         (_)__ \             | |    						 #
# ___  ___ _   ) |_      _____| |__  						 #
#/ __|/ __| | / /\ \ /\ / / _ \ '_ \ 						 #
#\__ \ (__| |/ /_ \ V  V /  __/ |_) |						 #
#|___/\___|_|____| \_/\_/ \___|_.__/ 						 #
#JORGE ZULUAGA (C) 2011  							 #
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
# SQL
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

###################################################
#CREATE USER
###################################################
#In order to create data base user run:
# create user 'sci2web'@'localhost' identified by 'WebPoweredNDSA'
# grant all privileges on sci2web.* to 'sci2web'@'localhost'
# flush privileges
###################################################
#CREATE DATABASE AND GIVES PERMISSIONS
###################################################
drop database if exists sci2web;
drop database if exists Diffusion_dev;
create database sci2web;
grant all privileges on sci2web.* to 'sci2web'@'localhost';

###################################################
#CREATE TABLES
###################################################
use sci2web;
create table apps (
       app_code varchar(255) not null,
       creation_date date not null,
       primary key (app_code),
       app_extra1 varchar(255),       
       app_extra2 varchar(255),       
       app_extra3 varchar(255),       

       #LINKS
       users_emails_author varchar(255) not null,
       versions_codes varchar(255)       
);     

create table versions (
       version_code varchar(255),
       release_date date not null,
       primary key (version_code,apps_code),
       version_extra1 varchar(255),       
       version_extra2 varchar(255),       
       version_extra3 varchar(255),       
       
       #LINKS
       users_emails_contributor varchar(255) not null,
       apps_code varchar(255)
);

create table users (
       email varchar(255) not null,
       username varchar(255) not null,
       password varchar(255) not null,
       activate tinyint(1) not null default 0,
       actcode varchar(8),
       complete_information text,       
       user_extra1 varchar(255),       
       user_extra2 varchar(255),       
       user_extra3 varchar(255),       

       primary key (email),
       
       #LINKS
       versions_codes varchar(255)
);

create table runs (
       run_code char(8),
       run_hash char(32) not null,
       run_name varchar(255) not null,
       run_pinfo varchar(255) null,
       run_status tinyint not null,
       configuration_date datetime not null,
       permissions char(3) not null,
       run_extra1 varchar(255),       
       run_extra2 varchar(255),       
       run_extra3 varchar(255),       
       
       primary key(run_code),

       #LINKS
       versions_id int,
       users_email varchar(255)
);

create table Diffusion_dev (
       dbrunhash char(32) not null,
       dbauthor varchar(255),
       dbdate datetime not null,
       
       PeriodOutput int(11),
       BaseUnits float,
       OutputBasename varchar(100),
       PlotFile varchar(255),
       MeanDispersions float,
       Height float,
       Width float,
       NumberParticles int(11),
       MeanFreePath float,
       DelayTime float,
       QuerySleep tinyint(1),
       GeneralComments text,

       primary key (dbrunhash),
       #LINKS
       runs_runcode char(8)
);

###################################################
#POPULATE TABLES
###################################################
insert into users 
       (email,username,password,activate,complete_information)
       values 
       ('sci2web@gmail.com','Super User',
	'75cdf1233f0c840d49178f55cfe397d2','1','Sci2Web Organization');

insert into apps 
       (app_code,users_emails_author,creation_date,versions_codes)
       values
       ('Diffusion','test@sci2web.org;',date(now()),'dev;');

insert into versions
       (version_code,release_date,users_emails_contributor,
	apps_code)
       values
       ('dev',date(now()),'test@sci2web.org;','Diffusion');
