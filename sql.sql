

-- テーブル初期化
drop table 顧客;
drop table 電話番号;
drop table 連絡先区分;
drop table 生年月日;
drop table 来店記録;

-- 顧客テーブル
create table 顧客(
    顧客ID char(7) not null,
    姓 varchar(100) not null,
    名 varchar(100) not null,
    セイ varchar(100),
    メイ varchar(100),
    備考 text,
    primary key(顧客ID));

-- 連絡先区分
create table 連絡先区分(
    区分ID char(1) not null,
    区分名 varchar(30) not null,
    primary key(区分ID));

-- 電話番号テーブル
create table 電話番号(
    顧客ID char(7) not null,
    電話番号 varchar(11) not null,
    区分ID char(1) not null,
    primary key(顧客ID,区分ID),
    foreign key (顧客ID) references 顧客(顧客ID),
    foreign key (区分ID) references 連絡先区分(区分ID));

-- 生年月日テーブル
create table 生年月日(
    顧客ID char(7) not null,
    登録ID int not null,
    生年月日 date ,
    続柄 varchar(10),
    primary key(顧客ID,登録ID),
    foreign key (顧客ID) references 顧客(顧客ID));

-- 来店記録テーブル
create table 来店記録(
    顧客ID char(7) not null,
    日時 timestamp not null,
    人数 int,
    続柄 varchar(10),
    メニュー text,
    primary key(顧客ID));


insert into 連絡先区分 values(1,'自宅');
insert into 連絡先区分 values(2,'会社');
insert into 連絡先区分 values(3,'本人');
insert into 連絡先区分 values(4,'その他');
