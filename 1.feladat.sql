/*1. Kérdezze le az összes nettó eladott értéket (mezők: netto_osszesen)*/

SELECT sum(eladas_darabszama * netto_eladasi_egysegar) as netto_osszesen FROM `szoftver_eladas` 


/*2.  Kérdezze le a szerzőket, akikhez még nincs nyilvántartva szoftver (mezők: szerzo_id, szerzo_nev)*/

SELECT sz.szerzo_id, sz.szerzo_nev FROM `szerzo` as sz 
   LEFT OUTER JOIN szoftver_szerzoje szsz ON szsz.szerzo_id = sz.szerzo_id WHERE szsz.szerzo_id IS NULL 


/*3. Kérdezze le az összes bruttó eladott értéket szoftverenként, feltételezve,
 hogy minden egyes szoftver bruttó árát egyenként egészre kerekítve fizették ki. (mezők: szoftver_azonosito, megnevezes, brutto)*/

SELECT sz.szoftver_azonosito, sz.megnevezes, (sze.eladas_darabszama * (sze.netto_eladasi_egysegar * sze.afa_szazalek)) as brutto FROM `szoftver` as sz JOIN szoftver_eladas sze ON sz.szoftver_azonosito = sze.szoftver_azonosito 

/*4.  Kérdezze le azokat a szoftvereket, ahol az összes nettó eladás értéke nagyobb, mint 100,000.00
A legnagyobb bevételű szoftverekkel kezdve maximum 20-at.
(mezők: szoftver_azonosito, megnevezes, ossz_netto)*/
 
SELECT sz.szoftver_azonosito, sz.megnevezes, (sze.eladas_darabszama * sze.netto_eladasi_egysegar) as ossz_netto
FROM `szoftver` as sz 
INNER JOIN szoftver_eladas sze ON sz.szoftver_azonosito = sze.szoftver_azonosito 
HAVING ossz_netto > 100000 
ORDER BY ossz_netto DESC LIMIT 20

/*5. Kérdezze le az összes szoftver azonosítóját és megnevezését, mellé annak összes szerzőjét ", " (vessző és szóköz) elválasztással.
A “szerzok” mezőben a szerzők részesedés szerinti csökkenő sorrendben legyenek.
(mezők: szoftver_azonosito, megnevezes, szerzok)*/

SELECT sz.szoftver_azonosito, sz.megnevezes, GROUP_CONCAT(sze.szerzo_nev SEPARATOR ', ') as szerzok
FROM `szoftver` as sz 
LEFT JOIN szoftver_szerzoje szsz ON sz.szoftver_azonosito = szsz.szoftver_azonosito 
LEFT JOIN szerzo  sze ON szsz.szerzo_id = sze.szerzo_id 
GROUP BY sz.szoftver_azonosito


/*6. Kérdezze le az elmúlt 8 órában eladással rendelkező szoftvereket, és hogy utoljára mikor lett eladva belőlük
(mezők: szoftver_azonosito, megnevezes, utolso_eladas_idopontja)*/

SELECT sz.szoftver_azonosito, sz.megnevezes, MAX( sze.eladas_idopontja ) as utolso_eladas_idopontja 
FROM `szoftver` as sz  
INNER JOIN szoftver_eladas sze ON sz.szoftver_azonosito = sze.szoftver_azonosito 
WHERE   sze.eladas_idopontja > DATE_SUB(NOW(), INTERVAL 8 HOUR) 
GROUP BY sz.szoftver_azonosito

/*7. Legnagyobb nettó bevételt termelő szerzők toplistájának 3. oldalát, ha egy oldalon 10 szoftver szerepel, a szerzők szoftverekben lévő részesedése és a szoftverek eladási adatai alaján.
(mezők: szerzo_nev, netto_bevetel)*/

SELECT  szerzo.szerzo_nev , ( ( (sze.eladas_darabszama * sze.netto_eladasi_egysegar) / 100) * szoftver_szerzoje.reszesedes) as  netto_bevetel FROM szerzo
JOIN szoftver_szerzoje ON szoftver_szerzoje.szerzo_id = szerzo.szerzo_id 
JOIN szoftver_eladas sze ON sze.szoftver_azonosito = szoftver_szerzoje.szoftver_azonosito 
LIMIT 20, 10

/*Készítsen külső kulcs megszorításokat az adatbázis megfelelő mezőire:*/
ALTER TABLE `szoftver_eladas` ADD  FOREIGN KEY (`szoftver_azonosito`) REFERENCES `szoftver`(`szoftver_azonosito`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `szoftver_szerzoje` ADD  FOREIGN KEY (`szerzo_id`) REFERENCES `szerzo`(`szerzo_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `szoftver_szerzoje` ADD FOREIGN KEY (`szoftver_azonosito`) REFERENCES `szoftver`(`szoftver_azonosito`) ON DELETE RESTRICT ON UPDATE CASCADE;


