create database test;

drop database test;



//////////////////////////////////////////////////////



drop table linkData;

create table if not exists `linkData`(
    `link` varchar(20) not null
);

insert into linkdata
    (link)
    values
    ('test');



/////////////////////////////////////////////////////////////////////////////////////////////



drop table user;

create table if not exists `user`(
    `id` varchar(20) not null,
    `password` varchar(20) not null
);

insert into user
    (id,password)
    values
    ('test','test');