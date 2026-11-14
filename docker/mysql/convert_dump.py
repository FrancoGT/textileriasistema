#!/usr/bin/env python3
"""
Convierte db/textileria.sql (dump con identificadores entre comillas simples,
SQL invalido) a un script valido para MariaDB 10.11 / MySQL 8:

  * 'identificador'  ->  `identificador`  (tablas, columnas, indices, vistas)
  * Los valores literales (INSERT VALUES, DEFAULT 'x', comparaciones) no se tocan
  * Quita CREATE DATABASE/USE (la BD la crea el contenedor)
  * Reemplaza DEFINER del hosting original por SQL SECURITY INVOKER
  * Omite restauraciones de @saved_* que nunca se asignaron (dump incompleto)

Uso:  python docker/mysql/convert_dump.py db/textileria.sql docker/mysql/init/01-textileria.sql
"""
import re
import sys

ID = r"'([A-Za-z_][A-Za-z0-9_]*)'"


def bt(m):
    return '`' + m.group(1) + '`'


def convert_view(line):
    # 'A'.'COL'  -> `A`.`COL`
    line = re.sub(ID + r"\." + ID, lambda m: f"`{m.group(1)}`.`{m.group(2)}`", line)
    # AS 'ALIAS' / VIEW 'NAME'
    line = re.sub(r"\b(AS|VIEW)\s+" + ID, lambda m: f"{m.group(1)} `{m.group(2)}`", line)
    # from/join 'TABLA' 'ALIAS'
    line = re.sub(r"\b(from|join)\s+(\(*)" + ID + r"\s+" + ID,
                  lambda m: f"{m.group(1)} {m.group(2)}`{m.group(3)}` `{m.group(4)}`", line)
    line = re.sub(r"\b(from|join)\s+(\(*)" + ID, lambda m: f"{m.group(1)} {m.group(2)}`{m.group(3)}`", line)
    return line


def convert(src, dst):
    out = []
    saved = set()  # variables @saved_* realmente asignadas
    with open(src, encoding='utf-8') as f:
        for line in f:
            s = line.rstrip('\n')
            if re.match(r'^(CREATE DATABASE|USE )', s):
                continue
            m = re.search(r"SET (@saved_\w+)\s*=\s*@@", s)
            if m:
                saved.add(m.group(1))
            m = re.search(r"SET \w+\s*=\s*(@saved_\w+)", s)
            if m and m.group(1) not in saved:
                continue  # restauracion sin asignacion previa (dump editado a mano)

            if s.startswith('INSERT INTO'):
                s = re.sub(r"^INSERT INTO " + ID, lambda m: f"INSERT INTO `{m.group(1)}`", s)
            elif 'DEFINER=' in s:
                s = '/*!50013 SQL SECURITY INVOKER */'
            elif re.search(r"\bVIEW\b.*\bAS\s*\(?\s*select\b", s, re.I):
                s = convert_view(s)
            elif re.match(r"^\s+'", s):
                # definicion de columna: solo el nombre (inicio de linea)
                s = re.sub(r"^(\s+)" + ID, lambda m: f"{m.group(1)}`{m.group(2)}`", s)
            elif re.search(r"(CREATE TABLE|DROP TABLE|DROP VIEW|ALTER TABLE|LOCK TABLES|PRIMARY KEY|UNIQUE KEY|^\s*KEY|CONSTRAINT|FOREIGN KEY|REFERENCES)", s):
                s = re.sub(ID, bt, s)
            out.append(s)

    with open(dst, 'w', encoding='utf-8', newline='\n') as f:
        f.write('-- Generado por docker/mysql/convert_dump.py a partir de db/textileria.sql\n')
        f.write('SET NAMES utf8mb4;\n')
        f.write('\n'.join(out) + '\n')


if __name__ == '__main__':
    convert(sys.argv[1], sys.argv[2])
