create table pt_zendesk(
  informationcollection_id int not null primary key,
  retry_count int not null,
  status varchar(10),     /* FAIL, SUCCESS, RETRY */
  error varchar (1000)    /* Exception message */
);

