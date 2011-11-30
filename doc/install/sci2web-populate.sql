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
use sci2web;
insert into users 
       (email,username,password,activate,complete_information)
       values 
       ('sci2web@gmail.com','Super User',
	'75cdf1233f0c840d49178f55cfe397d2','1','Sci2Web Organization');

insert into apps 
       (app_code_name,app_complete_name,
       users_emails_author,brief_description,creation_date,versions_ids)
       values
       ('Diffusion','Monte Carlo Diffusion',
       'test@sci2web.org;',
       'Simulate the diffusion of dust particles in rectangular box',
       date(now()),'1;');

insert into versions
       (version_code,release_date,users_emails_contributor,
       changes_log,apps_code_name)
       values
       ('dev',date(now()),'test@sci2web.org;',
       'New version','Diffusion');
