create database kugel;

create table categorias (
    id int auto_increment,
    nome varchar(50) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);

create table problemas (
    id int auto_increment,
    titulo varchar(255) not null,
    situacao varchar(1000) not null,
    solucao varchar(1000) not null,
    criador varchar(30) not null,
    categoria_id int,
    created_at timestamp,
    updated_at timestamp,
    primary key(id),
    foreign key(categoria_id) references categorias(id),
);

create table tags (
    id int auto_increment,
    nome varchar(50) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);

create table problemas_tags (
    id int auto_increment,
    problema_id int not null,
    tag_id int not null,
    created_at timestamp,
    updated_at timestamp,
    foreign key(problema_id) references problemas(id),
    foreign key(tag_id) references tags(id),
    primary key(id)
);

-- Limpar
delete from tags where id not in (select tag_id from problemas_tags join problemas on (problemas.id = problemas_tags.problema_id));
delete from tabelas where id not in (select tabela_id from problemas_tabelas join problemas on (problemas.id = problemas_tabelas.problema_id));

create table tabelas (
    id int auto_increment,
    nome varchar(50) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);

create table problemas_tabelas (
    id int auto_increment,
    problema_id int not null,
    tabela_id int not null,
    created_at timestamp,
    updated_at timestamp,
    foreign key(problema_id) references problemas(id),
    foreign key(tabela_id) references tabelas(id),
    primary key(id)
);

-- tabelas para dados da nfe
create table contingencias_ativadas (
    id int auto_increment,
    texto varchar(500) not null,
    visto char(1) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);

create table contingencias_agendadas (
    id int auto_increment,
    texto varchar(500) not null,
    visto char(1) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);

create table informes (
    id int auto_increment,
    texto varchar(500) not null,
    visto char(1) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);

create table documentos_diversos (
    id int auto_increment,
    texto varchar(500) not null,
    visto char(1) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);

create table documentos_tecnicos (
    id int auto_increment,
    texto varchar(500) not null,
    visto char(1) not null,
    created_at timestamp,
    updated_at timestamp,
    primary key(id)
);