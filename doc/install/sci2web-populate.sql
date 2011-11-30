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
use sci2webpp;
insert into users 
       (email,username,password,activate,complete_information)
       values 
       ('zuluagajorge@gmail.com','Jorge Zuluaga',
	'202cb962ac59075b964b07152d234b70','1','Universidad de Antioquia');

insert into apps 
       (app_code_name,app_complete_name,
       users_emails_author,brief_description,creation_date,versions_ids)
       values
       ('Diffusion','Monte Carlo Diffusion',
       'zuluagajorge@gmail.com;',
       'Simulate the diffusion of dust particles in rectangular box',
       date(now()),'1;');

insert into versions
       (version_code,release_date,users_emails_contributor,
       changes_log,apps_code_name)
       values
       ('dev',date(now()),'zuluagajorge@gmail.com;',
       'New version','Diffusion');
