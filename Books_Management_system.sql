create table book
(
    bno char(8),
    category char(10),
    title varchar(40),
    press varchar(30),
    year int,
    author varchar(20),
    price numeric(7,2),
    total int,
    stock int,
    primary key(bno)
)character set = utf8;
create table card
(
    cno char(7),
    name varchar(10),
    department varchar(40),
    type char(1),
    primary key(cno),
    check(type in ('T','G','U','O'))
)character set = utf8;
create table manager
(
    mgrID char(10),
    password char(40),
    name varchar(20),
    phone_number char(11),
    primary key(mgrID) 
)character set = utf8;
create table borrow
(
    cno char(7),
    bno char(8),
    borrow_date date,
    return_date date,
    mgrID char(10),
    foreign key(bno) references book(bno) on delete cascade,
    foreign key(cno) references card(cno) on update cascade,
    foreign key(mgrID) references manager(mgrID) on update cascade
)character set = utf8;