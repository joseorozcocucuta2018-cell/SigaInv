USE sigainv;

-- =====================================================
-- SEED PRODUCTOS SPEEDLOGIC - 2026-04-06 (v5)
-- Precios: precio_compra tomado de scripts/productos.txt (× 1000)
-- precio_venta = precio_compra × 1.25 (margen 25%)
-- costo_promedio = precio_compra (estado inicial)
-- 10 categorías | 21 marcas | 119 productos
-- 5 proveedores mayoristas | 15 clientes Cúcuta
-- 5 compras | 0 ventas | 2 traslados | 9 remisiones
-- Pagos: 10 clientes | 4 proveedores
-- 6 fórmulas de transformación (4 simuladores + 2 streaming)
-- tipo_movimiento inicial: saldo_inicial (requiere migrate:fresh previo)
-- =====================================================

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE detalle_ajustes_inventario;
TRUNCATE TABLE ajustes_inventario;
TRUNCATE TABLE detalle_conteos_fisicos;
TRUNCATE TABLE conteos_fisicos;
TRUNCATE TABLE movimientos_inventario;
TRUNCATE TABLE stock_bodega_serials;
TRUNCATE TABLE stock_bodega_lotes;
TRUNCATE TABLE stock_bodegas;
TRUNCATE TABLE detalles_devoluciones;
TRUNCATE TABLE devoluciones;
TRUNCATE TABLE detalle_pago_proveedores;
TRUNCATE TABLE pago_proveedores;
TRUNCATE TABLE detalle_pago_clientes;
TRUNCATE TABLE pago_clientes;
TRUNCATE TABLE detalle_compras;
TRUNCATE TABLE compras;
TRUNCATE TABLE detalle_ventas;
TRUNCATE TABLE ventas;
TRUNCATE TABLE detalle_remisiones;
TRUNCATE TABLE remisiones;
TRUNCATE TABLE detalle_cotizaciones;
TRUNCATE TABLE cotizaciones;
TRUNCATE TABLE historico_precios;
TRUNCATE TABLE formula_transformacion_detalles;
TRUNCATE TABLE formula_transformaciones;
TRUNCATE TABLE productos;
TRUNCATE TABLE marcas;
TRUNCATE TABLE categorias;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CATEGORÍAS (10)
-- =====================================================
INSERT IGNORE INTO categorias (id,categoria_id,nombre,descripcion,activo,created_at,updated_at) VALUES
(1,NULL,'SMARTWATCH',NULL,1,NOW(),NOW()),
(2,NULL,'CONSOLAS',NULL,1,NOW(),NOW()),
(3,NULL,'PORTATILES',NULL,1,NOW(),NOW()),
(4,NULL,'EQUIPOS ENSAMBLADOS',NULL,1,NOW(),NOW()),
(5,NULL,'WEBCAM',NULL,1,NOW(),NOW()),
(6,NULL,'STREAMING',NULL,1,NOW(),NOW()),
(7,NULL,'SIMULADORES',NULL,1,NOW(),NOW()),
(8,NULL,'SOPORTES',NULL,1,NOW(),NOW()),
(9,NULL,'REDES',NULL,1,NOW(),NOW()),
(10,NULL,'CONECTIVIDAD',NULL,1,NOW(),NOW());

-- =====================================================
-- MARCAS (21)
-- =====================================================
INSERT IGNORE INTO marcas (id,nombre,descripcion,activo,created_at,updated_at) VALUES
(1,'ANTEC',NULL,1,NOW(),NOW()),
(2,'ASUS',NULL,1,NOW(),NOW()),
(3,'COOLER MASTER',NULL,1,NOW(),NOW()),
(4,'COUGAR',NULL,1,NOW(),NOW()),
(5,'CYCLON',NULL,1,NOW(),NOW()),
(6,'ELGATO',NULL,1,NOW(),NOW()),
(7,'GIGABYTE',NULL,1,NOW(),NOW()),
(8,'HYPERX',NULL,1,NOW(),NOW()),
(9,'LENOVO',NULL,1,NOW(),NOW()),
(10,'LOGITECH',NULL,1,NOW(),NOW()),
(11,'MSI',NULL,1,NOW(),NOW()),
(12,'NB',NULL,1,NOW(),NOW()),
(13,'PXN',NULL,1,NOW(),NOW()),
(14,'REDRAGON',NULL,1,NOW(),NOW()),
(15,'SOLIDVIEW',NULL,1,NOW(),NOW()),
(16,'STARTEC',NULL,1,NOW(),NOW()),
(17,'STREAMPLIFY',NULL,1,NOW(),NOW()),
(18,'TP-LINK',NULL,1,NOW(),NOW()),
(19,'TPLINK',NULL,1,NOW(),NOW()),
(20,'VORTEX',NULL,1,NOW(),NOW()),
(21,'XIAOMI',NULL,1,NOW(),NOW());

-- =====================================================
-- PROVEEDORES MAYORISTAS TECNOLOGÍA (5 nuevos, IDs 9-13)
-- =====================================================
INSERT IGNORE INTO proveedores
(id,nombre,documento,tipo_documento,telefono,email,direccion1,departamento_id,ciudad_id,saldo,pais,activo,limite_credito,dias_credito,dias_pago,contacto_principal,sitio_web,usuario_id,created_at,updated_at)
VALUES
(9,'Sigma Sistemas S.A.S.','830019876-1','NIT','6015551001','ventas@sigmasistemas.com.co','Cra 13 # 93-40 Of. 501',11,174,0.00,'Colombia',1,50000000.00,30,30,'Carlos Bermúdez',NULL,1,NOW(),NOW()),
(10,'Secundam S.A.S.','900112345-2','NIT','6015552002','info@secundam.com.co','Av. El Dorado # 68B-35 Bod. 12',11,174,0.00,'Colombia',1,80000000.00,30,30,'Patricia Vargas',NULL,1,NOW(),NOW()),
(11,'Distecno Colombia S.A.S.','900234567-3','NIT','6015553003','compras@distecno.co','Cra 68D # 22-31 Zona Industrial',11,174,0.00,'Colombia',1,40000000.00,30,30,'Mauricio Ríos',NULL,1,NOW(),NOW()),
(12,'Tech Trading Colombia S.A.S.','901345678-4','NIT','6015554004','pedidos@techtrading.com.co','Cll 72 # 10-07 Of. 301',11,174,0.00,'Colombia',1,60000000.00,30,30,'Andrea Salcedo',NULL,1,NOW(),NOW()),
(13,'Microsistemas del Norte S.A.S.','800456789-5','NIT','6075555005','ventas@microsistemasnorte.co','Av. 0 # 10-50 P.H.',54,889,0.00,'Colombia',1,30000000.00,15,15,'Jairo Contreras',NULL,1,NOW(),NOW());

-- =====================================================
-- CLIENTES EN CÚCUTA (15 nuevos, IDs 11-25)
-- =====================================================
INSERT IGNORE INTO clientes
(id,nombre,documento,tipo_documento,telefono,email,direccion1,departamento_id,ciudad_id,saldo,pais,activo,limite_credito,dias_credito,dias_pago,contacto_principal,porcentaje_descuento,portal_acceso,usuario_id,created_at,updated_at)
VALUES
(11,'Tecnomax Cúcuta S.A.S.','901234567-1','NIT','6075561011','compras@tecnomaxcucuta.co','Av. Libertadores # 12-45 Local 5',54,889,0.00,'Colombia',1,20000000.00,30,0,'Rodrigo Angarita',5.00,'sin_acceso',1,NOW(),NOW()),
(12,'Sistemas y Redes del Norte S.A.S.','900345678-2','NIT','6075562022','info@sredesdelnnorte.co','Cll 10 # 4-32 Of. 201',54,889,0.00,'Colombia',1,15000000.00,30,0,'Claudia Hernández',3.00,'sin_acceso',1,NOW(),NOW()),
(13,'Cibermax Computadores','901456789-3','NIT','6075563033','ventas@cibermaxcucuta.com','Cc Ventura Plaza Local 142',54,889,0.00,'Colombia',1,10000000.00,15,0,'Fabio Montañez',0.00,'sin_acceso',1,NOW(),NOW()),
(14,'PC Mania Cúcuta','900567890-4','NIT','3175564044','pcmania@cucuta.com','Cra 8 # 11-25 Local 3',54,889,0.00,'Colombia',1,5000000.00,0,0,'Edwin Pinto',0.00,'sin_acceso',1,NOW(),NOW()),
(15,'Empresa TechOriente S.A.S.','901678901-5','NIT','6075565055','gerencia@techoriente.com.co','Cll 0 # 8-15 Edificio Centro',54,889,0.00,'Colombia',1,25000000.00,45,0,'Olga Suárez',5.00,'sin_acceso',1,NOW(),NOW()),
(16,'Comunicaciones y Sistemas SAS','900789012-6','NIT','6075566066','admin@comysistemas.co','Av. Gran Colombia # 7-70',54,889,0.00,'Colombia',1,8000000.00,15,0,'Nelson Durán',0.00,'sin_acceso',1,NOW(),NOW()),
(17,'Distribuciones TechNorte','901890123-7','NIT','3175567077','techn@distribucion.co','Zona Industrial Vía Bucaramanga Km 2',54,889,0.00,'Colombia',1,12000000.00,30,0,'Miriam Cáceres',3.00,'sin_acceso',1,NOW(),NOW()),
(18,'Inversiones Digitales Norte S.A.S.','901901234-8','NIT','6075568088','contact@idnorte.com.co','Cll 14 # 3-58 Local 2',54,889,0.00,'Colombia',1,20000000.00,30,0,'Hernando Ospino',5.00,'sin_acceso',1,NOW(),NOW()),
(19,'Juan Carlos Rincón Prada','88201234','CC','3185561919','jcrincon@gmail.com','Bario El Salado Cra 15 # 18-30',54,889,0.00,'Colombia',1,0.00,0,0,NULL,0.00,'sin_acceso',1,NOW(),NOW()),
(20,'Sandra Milena Pedraza Torres','60381456','CC','3115562020','sandrapedrazat@gmail.com','Barrio La Primavera Cll 22 # 5-12',54,889,0.00,'Colombia',1,0.00,0,0,NULL,0.00,'sin_acceso',1,NOW(),NOW()),
(21,'Diego Fernando Castellanos Mora','88102789','CC','3225563131','dcastellanos.mora@gmail.com','Barrio Cerro Norte Cra 2 # 8-44',54,889,0.00,'Colombia',1,0.00,0,0,NULL,0.00,'sin_acceso',1,NOW(),NOW()),
(22,'Karol Yohana Suárez Díaz','60295123','CC','3115564242','karolsuarez.diaz@gmail.com','Barrio San Rafael Cll 7 # 3-21',54,889,0.00,'Colombia',1,0.00,0,0,NULL,0.00,'sin_acceso',1,NOW(),NOW()),
(23,'Pedro Ignacio Contreras Leal','88301456','CC','3185565353','pcontreras.leal@gmail.com','Barrio Chapinero Cra 20 # 15-10',54,889,0.00,'Colombia',1,0.00,0,0,NULL,0.00,'sin_acceso',1,NOW(),NOW()),
(24,'Laura Patricia Ortega Molina','60482789','CC','3115566464','laurortega.molina@gmail.com','Barrio Caobos Cll 5 # 9-33',54,889,0.00,'Colombia',1,0.00,0,0,NULL,0.00,'sin_acceso',1,NOW(),NOW()),
(25,'Andrés Mauricio García Cáceres','88403012','CC','3225567575','andresm.garcia@gmail.com','Barrio Belén Cra 11 # 4-18',54,889,0.00,'Colombia',1,0.00,0,0,NULL,0.00,'sin_acceso',1,NOW(),NOW());

-- =====================================================
-- BODEGAS (2) — requeridas por stock_bodegas
-- departamento_id=54 (Norte de Santander), ciudad_id=889 (Cúcuta)
-- =====================================================
INSERT IGNORE INTO bodegas
(id, nombre, descripcion, direccion1, direccion2, departamento_id, ciudad_id, activo, es_principal, usuario_id, created_at, updated_at) VALUES
(1, 'BODEGA PRINCIPAL',  'Bodega principal SpeedLogic Cúcuta', 'Av. Libertadores # 15-40 Bod. 1', NULL, 54, 889, 1, 1, 1, NOW(), NOW()),
(2, 'Bodega Secundaria', 'Bodega secundaria SpeedLogic Cúcuta', 'Cra. 7 # 5-23 Zona Industrial',   NULL, 54, 889, 1, 0, 1, NOW(), NOW());

-- =====================================================
-- PRODUCTOS (119)
-- nombre  = marca + modelo (identificador corto)
-- descripcion = especificaciones técnicas completas
-- precio_compra = precio de costo del distribuidor (fuente: productos.txt)
-- precio_venta = precio_compra × 1.25 (margen 25%)
-- costo_promedio = precio_compra (estado inicial antes de compras)
-- =====================================================
INSERT IGNORE INTO productos
(id,codigo,codigo_barras,nombre,tipo_producto,descripcion,precio_compra,costo_promedio,precio_venta,stock_minimo,stock_maximo,activo,exige_lote,exige_serial,categoria_id,marca_id,unidad_medida_id,impuesto_id,usuario_id,created_at,updated_at)
VALUES
-- SMARTWATCH (1-11)
(1,'PROD-0001',NULL,'XIAOMI SMART BAND 9 ACTIVE NEGRO','comprado',NULL,105000,105000.0000,131250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(2,'PROD-0002',NULL,'XIAOMI SMART BAND 9 ACTIVE BEIGE WHITE','comprado',NULL,105000,105000.0000,131250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(3,'PROD-0003',NULL,'XIAOMI SMART BAND 9 NEGRA','comprado',NULL,145000,145000.0000,181250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(4,'PROD-0004',NULL,'XIAOMI SMART BAND 9 GLACIER SILVER','comprado',NULL,145000,145000.0000,181250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(5,'PROD-0005',NULL,'XIAOMI SMART BAND 9 ARCTIC BLUE','comprado',NULL,145000,145000.0000,181250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(6,'PROD-0006',NULL,'XIAOMI SMART BAND 10 GLACIER SILVER','comprado',NULL,189000,189000.0000,236250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(7,'PROD-0007',NULL,'XIAOMI SMART BAND 10 MIDNIGHT NEGRO','comprado',NULL,189000,189000.0000,236250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(8,'PROD-0008',NULL,'XIAOMI REDMI WATCH 5 LITE NEGRO','comprado',NULL,199000,199000.0000,248750,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(9,'PROD-0009',NULL,'XIAOMI REDMI WATCH 5 LITE LIGHT GOLD','comprado',NULL,199000,199000.0000,248750,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(10,'PROD-0010',NULL,'XIAOMI REDMI WATCH 5 GRIS','comprado',NULL,449000,449000.0000,561250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
(11,'PROD-0011',NULL,'XIAOMI REDMI WATCH 5 NEGRO','comprado',NULL,449000,449000.0000,561250,2,0,1,0,0,1,21,1,1,NULL,NOW(),NOW()),
-- CONSOLAS (12)
(12,'PROD-0012',NULL,'ASUS ROG ALLY RC73YA-NH002W','comprado','PORTABLE BLANCO 512GB',2289000,2289000.0000,2861250,1,0,1,0,0,2,2,1,1,NULL,NOW(),NOW()),
-- PORTÁTILES ASUS (13-31)
(13,'PROD-0013',NULL,'ASUS VIVOBOOK GO E1504FA-BQ2676','comprado','RYZ 3 7320U+8GB D5 NO EXP+NvMe 512GB+AMD GRAPHICS+15.6" FHD GRIS',1419000,1419000.0000,1773750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(14,'PROD-0014',NULL,'ASUS VIVOBOOK X1504VA-BQ2732','comprado','CORE i3 1315U+12GB D4+NvMe 512GB+INTEL GRAPHICS+15.6" FHD HUELLA GRIS',1699000,1699000.0000,2123750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(15,'PROD-0015',NULL,'ASUS VIVOBOOK X1504VA-BQ4375','comprado','CORE i3 1315U+8GB D4+NvMe 17B+INTEL GRAPHICS+15.6" FHD+ MORRAL+MOUSE GRIS',1739000,1739000.0000,2173750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(16,'PROD-0016',NULL,'ASUS VIVOBOOK GO E1504FA-BQ2334','comprado','RYZ 5 7520U+16GB D5+NvMe 512GB+AMD GRAPHICS+15.6" FHD+HUELLA+MORRAL+MOUSE GRIS',2049000,2049000.0000,2561250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(17,'PROD-0017',NULL,'ASUS VIVOBOOK M1502YA-BQ295','comprado','RYZ 7 5825U+16GB D4+NvMe 512GB+AMD GRAPHICS+15.6" FHD+HUELLA GRIS',2149000,2149000.0000,2686250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(18,'PROD-0018',NULL,'ASUS VIVOBOOK M1502YA-BQ923','comprado','RYZ 7 5825U+16GB D4+NvMe 17B+AMD GRAPHICS+15.6" FHD HUELLA GRIS',2259000,2259000.0000,2823750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(19,'PROD-0019',NULL,'ASUS X1504VA-E84556','comprado','CORE 5 120U+16GB D5+NvMe 512GB+INTEL GRAPHICS+15.6" TOUCH FHD AZUL',2369000,2369000.0000,2961250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(20,'PROD-0020',NULL,'ASUS VIVOBOOK X1605VA-MB2667','comprado','CORE i5 13420H+16GB D4+NvMe 17B+INTEL GRAPHICS+16" AZUL',2409000,2409000.0000,3011250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(21,'PROD-0021',NULL,'ASUS VIVOBOOK X1502VA-NJ929','comprado','CORE i7 13620H+16GB+NvMe 512GB+INTEL GRAPHICS+15.6" FHD AZUL',2759000,2759000.0000,3448750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(22,'PROD-0022',NULL,'ASUS TUF F16 FX607VJ-RL016','comprado','CORE i5 210H+16GB D4+NvMe 512GB+6GB RTX-3050+15.6" GRIS',3099000,3099000.0000,3873750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(23,'PROD-0023',NULL,'ASUS TUF A15 FA506NC-HN006','comprado','RYZ 5 7535HS+16GB D5+NvMe 512GB+4GB RTX-3050+15.6" FHD NEGRO',3169000,3169000.0000,3961250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(24,'PROD-0024',NULL,'ASUS ZENBOOK UM3406KA-QD227','comprado','RYZ AZI 7 350+16GB D5+NvMe 17B+AMD GRAPHICS+14" OLED WUXGA NEGRO',4439000,4439000.0000,5548750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(25,'PROD-0025',NULL,'ASUS 16 GAMER V3607VU-RP273','comprado','CORE 5 210H+32GB D5+NvMe 17B+6GB RTX-4050+16" WUXGA WV NEGRO MATE',4989000,4989000.0000,6236250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(26,'PROD-0026',NULL,'ASUS ZENBOOK UX3405CA-PZ332W','comprado','ULTRA 7 255H+16GB D5+NvMe 17B+INTEL GRAPHICS+OLED 14" 3K TOUCH+LAPIZ W11H AZUL',5299000,5299000.0000,6623750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(27,'PROD-0027',NULL,'ASUS TUF GAMING F16 FX608HR-RV006','comprado','CORE i7 14650HX+16GB D5+NvMe 17B+8GB RTX-5050+16" 165Hz WUXGA GRIS',6349000,6349000.0000,7936250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(28,'PROD-0028',NULL,'ASUS ROG G614PM-RV015W','comprado','RYZ 9 8940HX+32GB D5+NvMe 17B+8GB RTX-5060+16" WUXGA WINDOWS 11 HOME GRIS',7719000,7719000.0000,9648750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(29,'PROD-0029',NULL,'ASUS ROG G615LW-S5129W','comprado','ULTRA 9 275HX+32GB D5+NvMe 17B+16GB RTX-5080+16" 2.5K 240Hz WUXGA+MORRAL+MSE W11H GRIS',14439000,14439000.0000,18048750,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(30,'PROD-0030',NULL,'ASUS ROG G635LW-RW198W','comprado','ULTRA 9 275HX+64G+NvMe 27B+16GB RTX-5080+16" 2.5K 240Hz WUXGA+MORRAL+MSE+DIA W11H',15329000,15329000.0000,19161250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
(31,'PROD-0031',NULL,'ASUS ROG STRIX SCAR 18 GB35LX-SA137W','comprado','ULTRA 9 275HX+32GB+NvMe 27B+24GB RTX-5090+18" WUXGA 2.5K WIN 11 HOME',22669000,22669000.0000,28336250,1,0,1,0,0,3,2,1,1,NULL,NOW(),NOW()),
-- PORTÁTIL GIGABYTE (32)
(32,'PROD-0032',NULL,'GIGABYTE A16 CTH13LA8935H','comprado','CORE i7 13620H+16GB D5+NvMe 512GB+8GB RTX-5050+16" WUXGA FHD 165Hz W11H RGB+MORRAL',5459000,5459000.0000,6823750,1,0,1,0,0,3,7,1,1,NULL,NOW(),NOW()),
-- PORTÁTILES LENOVO (33-35)
(33,'PROD-0033',NULL,'LENOVO V14 G4 AMN','comprado','RYZ 5 7520U+16GB D5+R14S+NvMe 512GB+AMD GRAPHICS+14" FHD GRIS',1859000,1859000.0000,2323750,1,0,1,0,0,3,9,1,1,NULL,NOW(),NOW()),
(34,'PROD-0034',NULL,'LENOVO IDEAPAD SLIM 3 15AHP10','comprado','RYZ 5 8640HS AI+8GB D5+NvMe 512GB+12G+AMD GRAPHICS+15.3" WUXGA AZUL+MOUSE BLUETOOTH',2049000,2049000.0000,2561250,1,0,1,0,0,3,9,1,1,NULL,NOW(),NOW()),
(35,'PROD-0035',NULL,'LENOVO LOQ 15IAX9E','comprado','CORE i5 12450HX+16GB D5+NvMe 512GB+8GB RTX-3050+15.6" FHD GRIS',3099000,3099000.0000,3873750,1,0,1,0,0,3,9,1,1,NULL,NOW(),NOW()),
-- PORTÁTILES MSI (36-40)
(36,'PROD-0036',NULL,'MSI THIN A15 B7UC-621XCO','comprado','RYZ 5 7535HS+16GB D5+NvMe 512GB+4GB RTX-3050+15.6" FHD+MORRAL+2 AÑOS GTIA',3069000,3069000.0000,3836250,1,0,1,0,0,3,11,1,1,NULL,NOW(),NOW()),
(37,'PROD-0037',NULL,'MSI THIN 15 B13UC-3253XCO','comprado','CORE i7 13620H+16GB D4+NvMe 512GB+4GB RTX-3050+15.6" FHD+MORRAL+2 AÑOS GTIA',3389000,3389000.0000,4236250,1,0,1,0,0,3,11,1,1,NULL,NOW(),NOW()),
(38,'PROD-0038',NULL,'MSI CYBORG 15 B2RWEKG-031XCO','comprado','CORE 5 210H+16GB D5+NvMe 512GB+8GB RTX-5050+15.6" FHD 144Hz+MORRAL 2 AÑOS GTIA',5299000,5299000.0000,6623750,1,0,1,0,0,3,11,1,1,NULL,NOW(),NOW()),
(39,'PROD-0039',NULL,'MSI CYBORG A15 AI B2HWFKG-014XCO','comprado','RYZ 7 260+16GB D5+NvMe 512GB+8GB RTX-5060+15.6" FHD 144Hz+MORRAL 2 AÑOS GTIA',6249000,6249000.0000,7811250,1,0,1,0,0,3,11,1,1,NULL,NOW(),NOW()),
(40,'PROD-0040',NULL,'MSI KATANA 15 HX B14WFK-496XCO','comprado','CORE i7 14650HX+16GB D5+NvMe 512GB+8GB RTX-5060+15.6" FHD QHD 2K 165Hz MORRAL 2 AÑOS',6889000,6889000.0000,8611250,1,0,1,0,0,3,11,1,1,NULL,NOW(),NOW()),
-- EQUIPOS ENSAMBLADOS (41-48)
(41,'PROD-0041',NULL,'POWER CL-X18','manufacturado','V.T.+4ARC RYZ 5 5600GT+16GB D4+NvMe 512GB+600W B2+AS20M WIFI+ASUS 24" IPS FHD (1ms=146Hz)+T+M 801 RGB',2619000,2619000.0000,3273750,1,0,1,0,0,4,2,1,1,NULL,NOW(),NOW()),
(42,'PROD-0042',NULL,'POWER CL-X18','manufacturado','V.T.+4ARC RYZ 5 5600GT+16GB D4+NvMe 512GB+600W B2+ASUS TDF AS20M+ASUS 24" IPS FHD (1ms=146Hz)+T+M GENIUS KM-170',2619000,2619000.0000,3273750,1,0,1,0,0,4,2,1,1,NULL,NOW(),NOW()),
(43,'PROD-0043',NULL,'POWER CL-W82','manufacturado','CAJA V.T.+4ARC RYZ 5 5600GT+16GB D4+B550M WIFI+600W B2+NvMe 512GB+ASUS 24" IPS FHD (1ms=146Hz)+T+M 801 RGB',2725000,2725000.0000,3406250,1,0,1,0,0,4,2,1,1,NULL,NOW(),NOW()),
(44,'PROD-0044',NULL,'COUGAR MX220','manufacturado','V.T.+4ARC RYZ 5 8500GT+16GB D5+B650M-A WIFI+NvMe 500GB+GIGABYTE 24" SSIPS FHD (1ms=200Hz)+T+M EVC MK102R',2859000,2859000.0000,3573750,1,0,1,0,0,4,4,1,1,NULL,NOW(),NOW()),
(45,'PROD-0045',NULL,'COUGAR AIRFACE','manufacturado','V.T.+4ARC BFLIO RYZ 5 8500GT+16GB D5+RGB+B650 WIFI+650W B2+NvMe 480GB+MST 24" IPS FHD (0.5ms=200Hz)+T+M EVC MK102R',2859000,2859000.0000,3573750,1,0,1,0,0,4,4,1,1,NULL,NOW(),NOW()),
(46,'PROD-0046',NULL,'POWER CL-X18','manufacturado','V.T.+4ARC RYZ 5 7500GT+16GB D4 RGB+NvMe 512GB+600W B2+AS20M WIFI+ASUS 24" IPS FHD (1ms=146Hz)+T+M 801 RGB',2949000,2949000.0000,3686250,1,0,1,0,0,4,2,1,1,NULL,NOW(),NOW()),
(47,'PROD-0047',NULL,'COUGAR AIRFACE','manufacturado','V.T.+4ARC BFLIO RYZ 5 8600GT+16GB D5+RGB+NvMe 480GB+B650 WIFI+650W B2+MST 24" IPS FHD (0.5ms=200Hz)+T+M EVC MK102R',3049000,3049000.0000,3811250,1,0,1,0,0,4,4,1,1,NULL,NOW(),NOW()),
(48,'PROD-0048',NULL,'POWER CL-W82','manufacturado','CAJA V.T.+4ARC RYZ 5 7500GT+16GB D4 RGB+B550M WIFI+NvMe 512GB+ASUS 24" IPS FHD (1ms=146Hz)+T+M 801 RGB',3049000,3049000.0000,3811250,1,0,1,0,0,4,2,1,1,NULL,NOW(),NOW()),
-- WEBCAM (49-51)
(49,'PROD-0049',NULL,'LOGITECH MB RIO 4K ULTRA HD','comprado','CÁMARA WEB 4K',749000,749000.0000,936250,2,0,1,0,0,5,10,1,1,NULL,NOW(),NOW()),
(50,'PROD-0050',NULL,'LOGITECH MB RIO 4K ULTRA HD','comprado','CÁMARA WEB 4K',749000,749000.0000,936250,2,0,1,0,0,5,10,1,1,NULL,NOW(),NOW()),
(51,'PROD-0051',NULL,'LOGITECH C922 PRO HD STREAM','comprado','CÁMARA WEB 1080P HD 60FPS + TRÍPODE',339000,339000.0000,423750,2,0,1,0,0,5,10,1,1,NULL,NOW(),NOW()),
-- STREAMING (52-63)
(52,'PROD-0052',NULL,'ELGATO STREAM DECK MINI','comprado',NULL,289000,289000.0000,361250,2,0,1,0,0,6,6,1,1,NULL,NOW(),NOW()),
(53,'PROD-0053',NULL,'ELGATO WAVE NEO','comprado','MICRÓFONO BLANCO',329000,329000.0000,411250,2,0,1,0,0,6,6,1,1,NULL,NOW(),NOW()),
(54,'PROD-0054',NULL,'ELGATO STREAM DECK XL','comprado','CONTROL DE CONTENIDO USB 32 TECLAS',1209000,1209000.0000,1511250,1,0,1,0,0,6,6,1,1,NULL,NOW(),NOW()),
(55,'PROD-0055',NULL,'ELGATO GAME CAPTURE 4K PRO','comprado','CAPTURADORA DE VIDEO',1365000,1365000.0000,1706250,1,0,1,0,0,6,6,1,1,NULL,NOW(),NOW()),
(56,'PROD-0056',NULL,'ELGATO STREAM DECK NEO','comprado','PANEL CONTROL 8 TECLAS LCD',459000,459000.0000,573750,2,0,1,0,0,6,6,1,1,NULL,NOW(),NOW()),
(57,'PROD-0057',NULL,'REDRAGON S5550 STREAMCRAFT','comprado','PANEL CONTROL 15 COMANDOS LCD',379000,379000.0000,473750,2,0,1,0,0,6,14,1,1,NULL,NOW(),NOW()),
(58,'PROD-0058',NULL,'STREAMPLIFY DECK ONE','comprado','PANEL CONTROL 15 COMANDOS',419000,419000.0000,523750,2,0,1,0,0,6,17,1,1,NULL,NOW(),NOW()),
(59,'PROD-0059',NULL,'HYPERX SOLOCAST','comprado','MICRÓFONO USB NEGRO',180000,180000.0000,225000,2,0,1,0,0,6,8,1,1,NULL,NOW(),NOW()),
(60,'PROD-0060',NULL,'LOGITECH YETI BLUE ORB','comprado','MICRÓFONO USB LIGHTSYNC',249000,249000.0000,311250,2,0,1,0,0,6,10,1,1,NULL,NOW(),NOW()),
(61,'PROD-0061',NULL,'HYPERX QUADCAST 2','comprado','MICRÓFONO USB NEGRO',479000,479000.0000,598750,2,0,1,0,0,6,8,1,1,NULL,NOW(),NOW()),
(62,'PROD-0062',NULL,'LOGITECH YETI GX','comprado','MICRÓFONO USB LIGHTSYNC NEGRO',565000,565000.0000,706250,2,0,1,0,0,6,10,1,1,NULL,NOW(),NOW()),
(63,'PROD-0063',NULL,'REDRAGON GM300 BLAZAR','comprado','MICRÓFONO USB',205000,205000.0000,256250,2,0,1,0,0,6,14,1,1,NULL,NOW(),NOW()),
-- SIMULADORES (64-77)
(64,'PROD-0064',NULL,'LOGITECH DRIVING FORCE G29/G920','comprado','PALANCA CAMBIOS PARA TIMÓN',235000,235000.0000,293750,2,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(65,'PROD-0065',NULL,'VORTEX RDSC09BS03','comprado','SIMULADOR DE CARRERA',1219000,1219000.0000,1523750,1,0,1,0,0,7,20,1,1,NULL,NOW(),NOW()),
(66,'PROD-0066',NULL,'CYCLON RDSC07BSKPO1','comprado','SIMULADOR DE CARRERA',1939000,1939000.0000,2423750,1,0,1,0,0,7,5,1,1,NULL,NOW(),NOW()),
(67,'PROD-0067',NULL,'PXN V99','comprado','TIMÓN + PEDALES + PALANCA',1039000,1039000.0000,1298750,1,0,1,0,0,7,13,1,1,NULL,NOW(),NOW()),
(68,'PROD-0068',NULL,'PXN V10 PRO','comprado','TIMÓN + PEDALES',1119000,1119000.0000,1398750,1,0,1,0,0,7,13,1,1,NULL,NOW(),NOW()),
(69,'PROD-0069',NULL,'PXN V10','comprado','TIMÓN + PEDALES + PALANCA',1549000,1549000.0000,1936250,1,0,1,0,0,7,13,1,1,NULL,NOW(),NOW()),
(70,'PROD-0070',NULL,'LOGITECH G920','comprado','TIMÓN + PEDALES (PC-XBOX)',1069000,1069000.0000,1336250,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(71,'PROD-0071',NULL,'LOGITECH G29','comprado','TIMÓN + PEDALES (PC-PLAY)',1119000,1119000.0000,1398750,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(72,'PROD-0072',NULL,'LOGITECH G923','comprado','TIMÓN + PEDALES (PC-PLAY)',1339000,1339000.0000,1673750,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(73,'PROD-0073',NULL,'LOGITECH G923','comprado','TIMÓN + PEDALES (PC-XBOX)',1339000,1339000.0000,1673750,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(74,'PROD-0074',NULL,'LOGITECH G920 + DRIVING FORCE','comprado','TIMÓN + PEDALES + PALANCA (PC-XBOX)',1225000,1225000.0000,1531250,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(75,'PROD-0075',NULL,'LOGITECH G29 + DRIVING FORCE','comprado','TIMÓN + PEDALES + PALANCA (PC-PLAY)',1325000,1325000.0000,1656250,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(76,'PROD-0076',NULL,'LOGITECH G923 + DRIVING FORCE','comprado','TIMÓN + PEDALES + PALANCA (PC-PLAY)',1545000,1545000.0000,1931250,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
(77,'PROD-0077',NULL,'LOGITECH G923 + DRIVING FORCE','comprado','TIMÓN + PEDALES + PALANCA (PC-XBOX)',1545000,1545000.0000,1931250,1,0,1,0,0,7,10,1,1,NULL,NOW(),NOW()),
-- SOPORTES (78-87)
(78,'PROD-0078',NULL,'NB AS','comprado','SOPORTE MONITOR 17"-32" NEGRO',109000,109000.0000,136250,2,0,1,0,0,8,12,1,1,NULL,NOW(),NOW()),
(79,'PROD-0079',NULL,'NB AS-XS','comprado','SOPORTE MONITOR 17"-32" BLANCO',109000,109000.0000,136250,2,0,1,0,0,8,12,1,1,NULL,NOW(),NOW()),
(80,'PROD-0080',NULL,'NB F80','comprado','SOPORTE MONITOR 17"-30" NEGRO MATE',109000,109000.0000,136250,2,0,1,0,0,8,12,1,1,NULL,NOW(),NOW()),
(81,'PROD-0081',NULL,'NB F80-XE','comprado','SOPORTE MONITOR 17"-30" MULTIFUNCIONAL BLANCO',109000,109000.0000,136250,2,0,1,0,0,8,12,1,1,NULL,NOW(),NOW()),
(82,'PROD-0082',NULL,'NB F80-XM','comprado','SOPORTE MONITOR 17"-30" GULF BLUE',109000,109000.0000,136250,2,0,1,0,0,8,12,1,1,NULL,NOW(),NOW()),
(83,'PROD-0083',NULL,'NB F160 DUAL','comprado','SOPORTE MONITOR DUAL 17"-27" NEGRO MATE',194000,194000.0000,242500,2,0,1,0,0,8,12,1,1,NULL,NOW(),NOW()),
(84,'PROD-0084',NULL,'NB F160 DUAL','comprado','SOPORTE MONITOR DUAL 17"-27" BLANCO',205000,205000.0000,256250,2,0,1,0,0,8,12,1,1,NULL,NOW(),NOW()),
(85,'PROD-0085',NULL,'SOPORTE BASE TV PEDESTAL 32-70"','comprado',NULL,399000,399000.0000,498750,2,0,1,0,0,8,NULL,1,1,NULL,NOW(),NOW()),
(86,'PROD-0086',NULL,'ANTEC HOLDER ARGB','comprado','SOPORTE GPU VIDRIO TEMPLADO',105000,105000.0000,131250,2,0,1,0,0,8,1,1,1,NULL,NOW(),NOW()),
(87,'PROD-0087',NULL,'COOLER MASTER ATLAS ARGB','comprado','SOPORTE GPU BRACKET',165000,165000.0000,206250,2,0,1,0,0,8,3,1,1,NULL,NOW(),NOW()),
-- REDES (88-94)
(88,'PROD-0088',NULL,'CABLE RED CAT6 2MT','comprado',NULL,9000,9000.0000,11250,5,0,1,0,0,9,NULL,1,1,NULL,NOW(),NOW()),
(89,'PROD-0089',NULL,'CABLE RED CAT6 5MT','comprado',NULL,16000,16000.0000,20000,5,0,1,0,0,9,NULL,1,1,NULL,NOW(),NOW()),
(90,'PROD-0090',NULL,'CABLE RED CAT6 1.8MT 100% COBRE','comprado',NULL,23000,23000.0000,28750,5,0,1,0,0,9,NULL,1,1,NULL,NOW(),NOW()),
(91,'PROD-0091',NULL,'XIAOMI 4A AC1200','comprado','ROUTER DUAL BAND 4 ANTENAS',103000,103000.0000,128750,2,0,1,0,0,9,21,1,1,NULL,NOW(),NOW()),
(92,'PROD-0092',NULL,'TP-LINK TL-SG1016D','comprado','SWITCH 16 PUERTOS GIGABIT 1000',239000,239000.0000,298750,2,0,1,0,0,9,19,1,1,NULL,NOW(),NOW()),
(93,'PROD-0093',NULL,'TP-LINK ARCHER TX20E','comprado','ADAPTADOR PCIE WIFI 6 + BLUETOOTH 5.2',165000,165000.0000,206250,2,0,1,0,0,9,19,1,1,NULL,NOW(),NOW()),
(94,'PROD-0094',NULL,'TP-LINK ARCHER TX55E','comprado','ADAPTADOR PCIEXP AX3000 WIFI + BLUETOOTH 5.2',179000,179000.0000,223750,2,0,1,0,0,9,18,1,1,NULL,NOW(),NOW()),
-- CONECTIVIDAD (95-119)
(95,'PROD-0095',NULL,'ADAPTADOR HDMI A VGA + AUDIO','comprado',NULL,17000,17000.0000,21250,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(96,'PROD-0096',NULL,'ADAPTADOR USB-C A LAN 1000 RJ45','comprado','GENÉRICO',35000,35000.0000,43750,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(97,'PROD-0097',NULL,'ADAPTADOR USB 3.0 LAN 10/100/1000 RJ45','comprado','GENÉRICO',35000,35000.0000,43750,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(98,'PROD-0098',NULL,'ADAPTADOR 8EN1 TIPO-C','comprado','RED1000 HDTV+USB+RJ45+SD/TF CARD',79000,79000.0000,98750,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(99,'PROD-0099',NULL,'BLUETOOTH USB 5.4','comprado',NULL,22000,22000.0000,27500,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(100,'PROD-0100',NULL,'SOLIDVIEW HDMI 4K-8K 15M FIBRA','comprado','CABLE ACTIVO FIBRA ÓPTICA',225000,225000.0000,281250,2,0,1,0,0,10,15,1,1,NULL,NOW(),NOW()),
(101,'PROD-0101',NULL,'CABLE HDMI 1.8M 4K ENCAUCHETADO','comprado',NULL,18000,18000.0000,22500,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(102,'PROD-0102',NULL,'CABLE HDMI 3M 4K ENCAUCHETADO','comprado',NULL,23000,23000.0000,28750,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(103,'PROD-0103',NULL,'CABLE HDMI 5M 4K PREMIUM ENCAUCHETADO','comprado',NULL,29000,29000.0000,36250,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(104,'PROD-0104',NULL,'SOLIDVIEW HDMI 3M 4K 3D COBRE','comprado','CABLE HDMI CERTIFICADO',37000,37000.0000,46250,5,0,1,0,0,10,15,1,1,NULL,NOW(),NOW()),
(105,'PROD-0105',NULL,'SOLIDVIEW DISPLAY PORT 1.8M 4K-8K','comprado','CABLE COBRE CERTIFICADO',42000,42000.0000,52500,5,0,1,0,0,10,15,1,1,NULL,NOW(),NOW()),
(106,'PROD-0106',NULL,'SOLIDVIEW DISPLAY PORT 3M 4K-8K','comprado','CABLE COBRE CERTIFICADO',52000,52000.0000,65000,5,0,1,0,0,10,15,1,1,NULL,NOW(),NOW()),
(107,'PROD-0107',NULL,'SOLIDVIEW DISPLAY PORT 3M 4K-8K VER 2.1','comprado',NULL,59000,59000.0000,73750,5,0,1,0,0,10,15,1,1,NULL,NOW(),NOW()),
(108,'PROD-0108',NULL,'SOLIDVIEW DISPLAY PORT 5M 4K-8K','comprado',NULL,69000,69000.0000,86250,5,0,1,0,0,10,15,1,1,NULL,NOW(),NOW()),
(109,'PROD-0109',NULL,'CABLE SPLITTER RGB 1A4 FAN','comprado','GENÉRICO',19000,19000.0000,23750,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(110,'PROD-0110',NULL,'COOLER MASTER SPLITTER RGB 1A3 FAN','comprado','CABLE DIVISOR FAN',19000,19000.0000,23750,5,0,1,0,0,10,3,1,1,NULL,NOW(),NOW()),
(111,'PROD-0111',NULL,'CABLE CORRIENTE VENTILADOR 1A3','comprado',NULL,25000,25000.0000,31250,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(112,'PROD-0112',NULL,'COOLER MASTER RISER PCI 4.0 X16 200MM','comprado','CABLE EXTENSOR GPU V2',275000,275000.0000,343750,2,0,1,0,0,10,3,1,1,NULL,NOW(),NOW()),
(113,'PROD-0113',NULL,'COOLER MASTER RISER FLEX PCI 4.0 X16 300MM','comprado','CABLE EXTENSOR GPU V2',279000,279000.0000,348750,2,0,1,0,0,10,3,1,1,NULL,NOW(),NOW()),
(114,'PROD-0114',NULL,'CAJA EXTERNA M.2 3.1 GEN2 PCI-E GEN3','comprado','PORTÁTIL',75000,75000.0000,93750,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(115,'PROD-0115',NULL,'CAJA EXTERNA M.2 NVME/NGFF 3.1 USB A/C','comprado','PORTÁTIL',75000,75000.0000,93750,5,0,1,0,0,10,NULL,1,1,NULL,NOW(),NOW()),
(116,'PROD-0116',NULL,'STARTEC CARGADOR UNIVERSAL','comprado','CON ADAPTADOR PARA PORTÁTIL',65000,65000.0000,81250,5,0,1,0,0,10,16,1,1,NULL,NOW(),NOW()),
(117,'PROD-0117',NULL,'LOGITECH SPOTLIGHT','comprado','CONTROL REMOTO PRESENTACIÓN',315000,315000.0000,393750,2,0,1,0,0,10,10,1,1,NULL,NOW(),NOW()),
(118,'PROD-0118',NULL,'XIAOMI ELECTRIC AIR COMPRESSOR 2','comprado','COMPRESOR PORTABLE',189000,189000.0000,236250,2,0,1,0,0,10,21,1,1,NULL,NOW(),NOW()),
(119,'PROD-0119',NULL,'XIAOMI ELECTRIC AIR COMPRESSOR 2 PRO','comprado','COMPRESOR PORTABLE',229000,229000.0000,286250,2,0,1,0,0,10,21,1,1,NULL,NOW(),NOW());

-- =====================================================
-- MOVIMIENTOS: Saldo Inicial (2026-01-01)
-- costo_unitario = precio_compra del producto
-- =====================================================
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(1,1,'saldo_inicial',15.000,105000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(2,1,'saldo_inicial',15.000,105000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(3,1,'saldo_inicial',15.000,145000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(4,1,'saldo_inicial',15.000,145000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(5,1,'saldo_inicial',15.000,145000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(6,1,'saldo_inicial',15.000,189000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(7,1,'saldo_inicial',15.000,189000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(8,1,'saldo_inicial',2.000,199000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(9,1,'saldo_inicial',15.000,199000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(10,1,'saldo_inicial',15.000,449000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(11,1,'saldo_inicial',15.000,449000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(12,1,'saldo_inicial',1.000,2289000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(13,1,'saldo_inicial',5.000,1419000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(14,1,'saldo_inicial',3.000,1699000,NULL,NULL,3.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(15,1,'saldo_inicial',2.000,1739000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(16,1,'saldo_inicial',5.000,2049000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(17,1,'saldo_inicial',2.000,2149000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(18,1,'saldo_inicial',2.000,2259000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(19,1,'saldo_inicial',2.000,2369000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(20,1,'saldo_inicial',2.000,2409000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(21,1,'saldo_inicial',1.000,2759000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(22,1,'saldo_inicial',1.000,3099000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(23,1,'saldo_inicial',5.000,3169000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(24,1,'saldo_inicial',1.000,4439000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(25,1,'saldo_inicial',1.000,4989000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(26,1,'saldo_inicial',1.000,5299000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(27,1,'saldo_inicial',1.000,6349000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(28,1,'saldo_inicial',1.000,7719000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(29,1,'saldo_inicial',1.000,14439000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(30,1,'saldo_inicial',1.000,15329000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(31,1,'saldo_inicial',1.000,22669000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(32,1,'saldo_inicial',1.000,5459000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(33,1,'saldo_inicial',5.000,1859000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(34,1,'saldo_inicial',3.000,2049000,NULL,NULL,3.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(35,1,'saldo_inicial',1.000,3099000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(36,1,'saldo_inicial',5.000,3069000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(37,1,'saldo_inicial',3.000,3389000,NULL,NULL,3.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(38,1,'saldo_inicial',2.000,5299000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(39,1,'saldo_inicial',2.000,6249000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(40,1,'saldo_inicial',2.000,6889000,NULL,NULL,2.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(41,1,'saldo_inicial',1.000,2619000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(42,1,'saldo_inicial',1.000,2619000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(43,1,'saldo_inicial',1.000,2725000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(44,1,'saldo_inicial',1.000,2859000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(45,1,'saldo_inicial',1.000,2859000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(46,1,'saldo_inicial',1.000,2949000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(47,1,'saldo_inicial',1.000,3049000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(48,1,'saldo_inicial',1.000,3049000,NULL,NULL,1.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(49,1,'saldo_inicial',15.000,749000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(50,1,'saldo_inicial',15.000,749000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(51,1,'saldo_inicial',15.000,339000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(52,1,'saldo_inicial',15.000,289000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(53,1,'saldo_inicial',15.000,329000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(54,1,'saldo_inicial',5.000,1209000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(55,1,'saldo_inicial',5.000,1365000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(56,1,'saldo_inicial',15.000,459000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(57,1,'saldo_inicial',15.000,379000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(58,1,'saldo_inicial',15.000,419000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(59,1,'saldo_inicial',15.000,180000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(60,1,'saldo_inicial',15.000,249000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(61,1,'saldo_inicial',15.000,479000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(62,1,'saldo_inicial',15.000,565000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(63,1,'saldo_inicial',15.000,205000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(64,1,'saldo_inicial',5.000,235000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(65,1,'saldo_inicial',5.000,1219000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(66,1,'saldo_inicial',5.000,1939000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(67,1,'saldo_inicial',5.000,1039000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(68,1,'saldo_inicial',5.000,1119000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(69,1,'saldo_inicial',5.000,1549000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(70,1,'saldo_inicial',5.000,1069000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(71,1,'saldo_inicial',5.000,1119000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(72,1,'saldo_inicial',5.000,1339000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(73,1,'saldo_inicial',5.000,1339000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(74,1,'saldo_inicial',5.000,1225000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(75,1,'saldo_inicial',5.000,1325000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(76,1,'saldo_inicial',5.000,1545000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(77,1,'saldo_inicial',5.000,1545000,NULL,NULL,5.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(78,1,'saldo_inicial',15.000,109000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(79,1,'saldo_inicial',15.000,109000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(80,1,'saldo_inicial',15.000,109000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(81,1,'saldo_inicial',15.000,109000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(82,1,'saldo_inicial',15.000,109000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(83,1,'saldo_inicial',15.000,194000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(84,1,'saldo_inicial',15.000,205000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(85,1,'saldo_inicial',15.000,399000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(86,1,'saldo_inicial',15.000,105000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(87,1,'saldo_inicial',15.000,165000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(88,1,'saldo_inicial',30.000,9000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(89,1,'saldo_inicial',30.000,16000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(90,1,'saldo_inicial',30.000,23000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(91,1,'saldo_inicial',15.000,103000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(92,1,'saldo_inicial',15.000,239000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(93,1,'saldo_inicial',15.000,165000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(94,1,'saldo_inicial',15.000,179000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(95,1,'saldo_inicial',30.000,17000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(96,1,'saldo_inicial',30.000,35000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(97,1,'saldo_inicial',30.000,35000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(98,1,'saldo_inicial',30.000,79000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(99,1,'saldo_inicial',30.000,22000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(100,1,'saldo_inicial',15.000,225000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(101,1,'saldo_inicial',30.000,18000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(102,1,'saldo_inicial',30.000,23000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(103,1,'saldo_inicial',30.000,29000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(104,1,'saldo_inicial',30.000,37000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(105,1,'saldo_inicial',30.000,42000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(106,1,'saldo_inicial',30.000,52000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(107,1,'saldo_inicial',30.000,59000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(108,1,'saldo_inicial',30.000,69000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(109,1,'saldo_inicial',30.000,19000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(110,1,'saldo_inicial',30.000,19000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(111,1,'saldo_inicial',30.000,25000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(112,1,'saldo_inicial',15.000,275000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(113,1,'saldo_inicial',15.000,279000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(114,1,'saldo_inicial',30.000,75000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(115,1,'saldo_inicial',30.000,75000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(116,1,'saldo_inicial',30.000,65000,NULL,NULL,30.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(117,1,'saldo_inicial',15.000,315000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(118,1,'saldo_inicial',15.000,189000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW()),
(119,1,'saldo_inicial',15.000,229000,NULL,NULL,15.000,'ajuste_inicial',NULL,NULL,NULL,NULL,'Saldo inicial de inventario',NULL,'2026-01-01 08:00:00',NOW(),NOW());

-- =====================================================
-- COMPRAS (5)
-- =====================================================

-- COMP-00001 (Ingram Micro – wearables Xiaomi – PAGADO)
-- prod 3: 10u × 145.000=1.450.000 | prod 8: 10u × 199.000=1.990.000 → Total: 3.440.000
INSERT IGNORE INTO compras (id,numero,estado,confirmada_en,proveedor_id,bodega_id,usuario_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,saldo_pendiente,estado_pago,observaciones,created_at,updated_at) VALUES
(1,'COMP-00001','confirmada','2026-01-15 10:00:00',2,1,1,'2026-01-15 09:30:00',3440000.00,0.00,0.00,3440000.00,3440000.00,0.00,0.00,'pagado','Compra inicial Xiaomi wearables – Ingram Micro',NOW(),NOW());
INSERT IGNORE INTO detalle_compras (id,compra_id,producto_id,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(1,1,3,10.000,145000.00,0.00,1,1450000.00,NOW(),NOW()),
(2,1,8,10.000,199000.00,0.00,1,1990000.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(3,1,'entrada_compra',10.000,145000.00,NULL,NULL,25.000,'compra',1,1,NULL,NULL,NULL,1,'2026-01-15 10:00:00',NOW(),NOW()),
(8,1,'entrada_compra',10.000,199000.00,NULL,NULL,12.000,'compra',1,2,NULL,NULL,NULL,1,'2026-01-15 10:00:01',NOW(),NOW());

-- COMP-00002 (Intcomex – portátiles ASUS – pago parcial)
-- prod 13: 2u × 1.419.000 | prod 14: 2u × 1.699.000 | prod 15: 1u × 1.739.000
-- subtotal: 7.975.000 | IVA 19% s/ prods 13+14: 1.184.840 → Total: 9.159.840
INSERT IGNORE INTO compras (id,numero,estado,confirmada_en,proveedor_id,bodega_id,usuario_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,saldo_pendiente,estado_pago,observaciones,created_at,updated_at) VALUES
(2,'COMP-00002','confirmada','2026-01-22 14:00:00',3,1,1,'2026-01-22 13:00:00',7975000.00,0.00,1184840.00,9159840.00,9159840.00,1184840.00,5159840.00,'parcial','Portátiles ASUS Vivobook – Intcomex',NOW(),NOW());
INSERT IGNORE INTO detalle_compras (id,compra_id,producto_id,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(3,2,13,2.000,1419000.00,0.00,2,2838000.00,NOW(),NOW()),
(4,2,14,2.000,1699000.00,0.00,2,3398000.00,NOW(),NOW()),
(5,2,15,1.000,1739000.00,0.00,1,1739000.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(13,1,'entrada_compra',2.000,1419000.00,NULL,NULL,7.000,'compra',2,3,NULL,NULL,NULL,1,'2026-01-22 14:00:00',NOW(),NOW()),
(14,1,'entrada_compra',2.000,1699000.00,NULL,NULL,5.000,'compra',2,4,NULL,NULL,NULL,1,'2026-01-22 14:00:01',NOW(),NOW()),
(15,1,'entrada_compra',1.000,1739000.00,NULL,NULL,3.000,'compra',2,5,NULL,NULL,NULL,1,'2026-01-22 14:00:02',NOW(),NOW());

-- COMP-00003 (Computex – equipos ensamblados – pago parcial)
-- prod 41: 1u × 2.619.000 | prod 44: 1u × 2.859.000 | prod 46: 1u × 2.949.000
-- subtotal: 8.427.000 | IVA 19% s/ prods 41+44: 1.040.820 → Total: 9.467.820
INSERT IGNORE INTO compras (id,numero,estado,confirmada_en,proveedor_id,bodega_id,usuario_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,saldo_pendiente,estado_pago,observaciones,created_at,updated_at) VALUES
(3,'COMP-00003','confirmada','2026-02-05 11:00:00',4,1,1,'2026-02-05 10:00:00',8427000.00,0.00,1040820.00,9467820.00,9467820.00,1040820.00,6467820.00,'parcial','Equipos ensamblados gamer – Computex',NOW(),NOW());
INSERT IGNORE INTO detalle_compras (id,compra_id,producto_id,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(6,3,41,1.000,2619000.00,0.00,2,2619000.00,NOW(),NOW()),
(7,3,44,1.000,2859000.00,0.00,2,2859000.00,NOW(),NOW()),
(8,3,46,1.000,2949000.00,0.00,1,2949000.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(41,1,'entrada_compra',1.000,2619000.00,NULL,NULL,2.000,'compra',3,6,NULL,NULL,NULL,1,'2026-02-05 11:00:00',NOW(),NOW()),
(44,1,'entrada_compra',1.000,2859000.00,NULL,NULL,2.000,'compra',3,7,NULL,NULL,NULL,1,'2026-02-05 11:00:01',NOW(),NOW()),
(46,1,'entrada_compra',1.000,2949000.00,NULL,NULL,2.000,'compra',3,8,NULL,NULL,NULL,1,'2026-02-05 11:00:02',NOW(),NOW());

-- COMP-00004 (Sigma Sistemas – streaming y micrófonos – pago parcial)
-- prod 59: 10u × 180.000=1.800.000 | prod 56: 5u × 459.000=2.295.000 → Total: 4.095.000
INSERT IGNORE INTO compras (id,numero,estado,confirmada_en,proveedor_id,bodega_id,usuario_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,saldo_pendiente,estado_pago,observaciones,created_at,updated_at) VALUES
(4,'COMP-00004','confirmada','2026-02-12 11:00:00',9,1,1,'2026-02-12 10:00:00',4095000.00,0.00,0.00,4095000.00,4095000.00,0.00,2595000.00,'parcial','Micrófonos y paneles streaming – Sigma Sistemas',NOW(),NOW());
INSERT IGNORE INTO detalle_compras (id,compra_id,producto_id,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(9,4,59,10.000,180000.00,0.00,1,1800000.00,NOW(),NOW()),
(10,4,56,5.000,459000.00,0.00,1,2295000.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(59,1,'entrada_compra',10.000,180000.00,NULL,NULL,25.000,'compra',4,9,NULL,NULL,NULL,1,'2026-02-12 11:00:00',NOW(),NOW()),
(56,1,'entrada_compra',5.000,459000.00,NULL,NULL,20.000,'compra',4,10,NULL,NULL,NULL,1,'2026-02-12 11:00:01',NOW(),NOW());

-- COMP-00005 (Distecno – redes y switches – pendiente)
-- prod 89: 20u × 16.000=320.000 | prod 92: 10u × 239.000=2.390.000 → Total: 2.710.000
INSERT IGNORE INTO compras (id,numero,estado,confirmada_en,proveedor_id,bodega_id,usuario_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,saldo_pendiente,estado_pago,observaciones,created_at,updated_at) VALUES
(5,'COMP-00005','confirmada','2026-02-18 11:00:00',11,1,1,'2026-02-18 10:00:00',2710000.00,0.00,0.00,2710000.00,2710000.00,0.00,2710000.00,'pendiente','Cables red CAT6 y switches – Distecno Colombia',NOW(),NOW());
INSERT IGNORE INTO detalle_compras (id,compra_id,producto_id,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(11,5,89,20.000,16000.00,0.00,1,320000.00,NOW(),NOW()),
(12,5,92,10.000,239000.00,0.00,1,2390000.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(89,1,'entrada_compra',20.000,16000.00,NULL,NULL,50.000,'compra',5,11,NULL,NULL,NULL,1,'2026-02-18 11:00:00',NOW(),NOW()),
(92,1,'entrada_compra',10.000,239000.00,NULL,NULL,25.000,'compra',5,12,NULL,NULL,NULL,1,'2026-02-18 11:00:01',NOW(),NOW());

-- =====================================================
-- TRASLADOS BODEGA 1 → BODEGA 2 (2)
-- =====================================================
INSERT IGNORE INTO traslados (id,bodega_origen_id,bodega_destino_id,estado,confirmada_en,fecha,observaciones,usuario_id,created_at,updated_at) VALUES
(1,1,2,'confirmada','2026-02-15 10:00:00','2026-02-15 09:30:00','Traslado wearables Xiaomi a bodega secundaria',1,NOW(),NOW()),
(2,1,2,'confirmada','2026-03-01 14:00:00','2026-03-01 13:00:00','Traslado portátiles ASUS a bodega secundaria',1,NOW(),NOW());
INSERT IGNORE INTO traslado_detalles (id,traslado_id,producto_id,cantidad,lote,fecha_vencimiento,created_at,updated_at) VALUES
(1,1,3,5.000,NULL,NULL,NOW(),NOW()),(2,1,8,3.000,NULL,NULL,NOW(),NOW()),(3,1,10,2.000,NULL,NULL,NOW(),NOW()),
(4,2,13,2.000,NULL,NULL,NOW(),NOW()),(5,2,14,1.000,NULL,NULL,NOW(),NOW()),(6,2,16,1.000,NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(3,1,'traslado_salida',5.000,145000.00,NULL,NULL,20.000,'traslado',1,NULL,NULL,NULL,'Salida traslado a bodega 2',NULL,'2026-02-15 10:00:00',NOW(),NOW()),
(8,1,'traslado_salida',3.000,199000.00,NULL,NULL,9.000,'traslado',1,NULL,NULL,NULL,'Salida traslado a bodega 2',NULL,'2026-02-15 10:00:01',NOW(),NOW()),
(10,1,'traslado_salida',2.000,449000.00,NULL,NULL,13.000,'traslado',1,NULL,NULL,NULL,'Salida traslado a bodega 2',NULL,'2026-02-15 10:00:02',NOW(),NOW()),
(3,2,'traslado_entrada',5.000,145000.00,NULL,NULL,5.000,'traslado',1,NULL,NULL,NULL,'Entrada traslado desde bodega 1',NULL,'2026-02-15 10:00:00',NOW(),NOW()),
(8,2,'traslado_entrada',3.000,199000.00,NULL,NULL,3.000,'traslado',1,NULL,NULL,NULL,'Entrada traslado desde bodega 1',NULL,'2026-02-15 10:00:01',NOW(),NOW()),
(10,2,'traslado_entrada',2.000,449000.00,NULL,NULL,2.000,'traslado',1,NULL,NULL,NULL,'Entrada traslado desde bodega 1',NULL,'2026-02-15 10:00:02',NOW(),NOW()),
(13,1,'traslado_salida',2.000,1419000.00,NULL,NULL,5.000,'traslado',2,NULL,NULL,NULL,'Salida traslado a bodega 2',NULL,'2026-03-01 14:00:00',NOW(),NOW()),
(14,1,'traslado_salida',1.000,1699000.00,NULL,NULL,4.000,'traslado',2,NULL,NULL,NULL,'Salida traslado a bodega 2',NULL,'2026-03-01 14:00:01',NOW(),NOW()),
(16,1,'traslado_salida',1.000,2049000.00,NULL,NULL,4.000,'traslado',2,NULL,NULL,NULL,'Salida traslado a bodega 2',NULL,'2026-03-01 14:00:02',NOW(),NOW()),
(13,2,'traslado_entrada',2.000,1419000.00,NULL,NULL,2.000,'traslado',2,NULL,NULL,NULL,'Entrada traslado desde bodega 1',NULL,'2026-03-01 14:00:00',NOW(),NOW()),
(14,2,'traslado_entrada',1.000,1699000.00,NULL,NULL,1.000,'traslado',2,NULL,NULL,NULL,'Entrada traslado desde bodega 1',NULL,'2026-03-01 14:00:01',NOW(),NOW()),
(16,2,'traslado_entrada',1.000,2049000.00,NULL,NULL,1.000,'traslado',2,NULL,NULL,NULL,'Entrada traslado desde bodega 1',NULL,'2026-03-01 14:00:02',NOW(),NOW());

-- =====================================================
-- REMISIONES (9) – usuario vendedor id=4
-- precio_unitario = precio_compra × 1.25, IVA 0%
-- =====================================================

-- REM-00001 (Tecnomax – 2026-03-05)
-- prod 13: 2u × 1.773.750=3.547.500 | prod 54: 1u × 1.511.250 → Total: 5.058.750 – PAGADO
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(1,'REM-00001','confirmada','2026-03-05 10:30:00',11,1,4,NULL,'2026-03-05 10:00:00',5058750.00,0.00,0.00,5058750.00,5058750.00,0.00,NULL,0.00,'pagado','2026-04-04',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(1,1,13,NULL,NULL,NULL,2.000,1773750.00,0.00,1,3547500.00,NOW(),NOW()),
(2,1,54,NULL,NULL,NULL,1.000,1511250.00,0.00,1,1511250.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(13,1,'salida_remision',2.000,1419000.00,NULL,NULL,3.000,'remision',1,NULL,NULL,1,NULL,4,'2026-03-05 10:30:00',NOW(),NOW()),
(54,1,'salida_remision',1.000,1209000.00,NULL,NULL,4.000,'remision',1,NULL,NULL,2,NULL,4,'2026-03-05 10:30:01',NOW(),NOW());

-- REM-00002 (PC Mania – 2026-03-08)
-- prod 3: 3u × 181.250=543.750 | prod 8: 2u × 248.750=497.500 → Total: 1.041.250 – PAGADO
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(2,'REM-00002','confirmada','2026-03-08 11:00:00',14,1,4,NULL,'2026-03-08 10:30:00',1041250.00,0.00,0.00,1041250.00,1041250.00,0.00,NULL,0.00,'pagado','2026-04-07',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(3,2,3,NULL,NULL,NULL,3.000,181250.00,0.00,1,543750.00,NOW(),NOW()),
(4,2,8,NULL,NULL,NULL,2.000,248750.00,0.00,1,497500.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(3,1,'salida_remision',3.000,145000.00,NULL,NULL,17.000,'remision',2,NULL,NULL,3,NULL,4,'2026-03-08 11:00:00',NOW(),NOW()),
(8,1,'salida_remision',2.000,199000.00,NULL,NULL,7.000,'remision',2,NULL,NULL,4,NULL,4,'2026-03-08 11:00:01',NOW(),NOW());

-- REM-00003 (Juan Carlos Rincón – 2026-03-12)
-- prod 36: 1u × 3.836.250 | prod 60: 1u × 311.250 → Total: 4.147.500 – PARCIAL (pago 1.500.000, saldo 2.647.500)
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(3,'REM-00003','confirmada','2026-03-12 10:30:00',19,1,4,NULL,'2026-03-12 10:00:00',4147500.00,0.00,0.00,4147500.00,4147500.00,0.00,NULL,2647500.00,'parcial','2026-04-11',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(5,3,36,NULL,NULL,NULL,1.000,3836250.00,0.00,1,3836250.00,NOW(),NOW()),
(6,3,60,NULL,NULL,NULL,1.000,311250.00,0.00,1,311250.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(36,1,'salida_remision',1.000,3069000.00,NULL,NULL,4.000,'remision',3,NULL,NULL,5,NULL,4,'2026-03-12 10:30:00',NOW(),NOW()),
(60,1,'salida_remision',1.000,249000.00,NULL,NULL,14.000,'remision',3,NULL,NULL,6,NULL,4,'2026-03-12 10:30:01',NOW(),NOW());

-- REM-00004 (Sistemas y Redes del Norte – 2026-03-15)
-- prod 92: 5u × 298.750=1.493.750 | prod 89: 10u × 20.000=200.000 | prod 91: 5u × 128.750=643.750
-- Total: 2.337.500 – PAGADO
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(4,'REM-00004','confirmada','2026-03-15 10:30:00',12,1,4,NULL,'2026-03-15 10:00:00',2337500.00,0.00,0.00,2337500.00,2337500.00,0.00,NULL,0.00,'pagado','2026-04-14',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(7,4,92,NULL,NULL,NULL,5.000,298750.00,0.00,1,1493750.00,NOW(),NOW()),
(8,4,89,NULL,NULL,NULL,10.000,20000.00,0.00,1,200000.00,NOW(),NOW()),
(9,4,91,NULL,NULL,NULL,5.000,128750.00,0.00,1,643750.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(92,1,'salida_remision',5.000,239000.00,NULL,NULL,20.000,'remision',4,NULL,NULL,7,NULL,4,'2026-03-15 10:30:00',NOW(),NOW()),
(89,1,'salida_remision',10.000,16000.00,NULL,NULL,40.000,'remision',4,NULL,NULL,8,NULL,4,'2026-03-15 10:30:01',NOW(),NOW()),
(91,1,'salida_remision',5.000,103000.00,NULL,NULL,10.000,'remision',4,NULL,NULL,9,NULL,4,'2026-03-15 10:30:02',NOW(),NOW());

-- REM-00005 (Cibermax Computadores – 2026-03-18)
-- prod 23: 1u × 3.961.250 | prod 65: 1u × 1.523.750 → Total: 5.485.000 – PARCIAL (pago 2.000.000, saldo 3.485.000)
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(5,'REM-00005','confirmada','2026-03-18 10:30:00',13,1,4,NULL,'2026-03-18 10:00:00',5485000.00,0.00,0.00,5485000.00,5485000.00,0.00,NULL,3485000.00,'parcial','2026-04-17',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(10,5,23,NULL,NULL,NULL,1.000,3961250.00,0.00,1,3961250.00,NOW(),NOW()),
(11,5,65,NULL,NULL,NULL,1.000,1523750.00,0.00,1,1523750.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(23,1,'salida_remision',1.000,3169000.00,NULL,NULL,4.000,'remision',5,NULL,NULL,10,NULL,4,'2026-03-18 10:30:00',NOW(),NOW()),
(65,1,'salida_remision',1.000,1219000.00,NULL,NULL,4.000,'remision',5,NULL,NULL,11,NULL,4,'2026-03-18 10:30:01',NOW(),NOW());

-- REM-00006 (Sandra Milena Pedraza – 2026-03-22)
-- prod 33: 1u × 2.323.750 | prod 83: 2u × 242.500=485.000 | prod 104: 1u × 46.250
-- Total: 2.855.000 – PAGADO
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(6,'REM-00006','confirmada','2026-03-22 10:30:00',20,1,4,NULL,'2026-03-22 10:00:00',2855000.00,0.00,0.00,2855000.00,2855000.00,0.00,NULL,0.00,'pagado','2026-04-21',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(12,6,33,NULL,NULL,NULL,1.000,2323750.00,0.00,1,2323750.00,NOW(),NOW()),
(13,6,83,NULL,NULL,NULL,2.000,242500.00,0.00,1,485000.00,NOW(),NOW()),
(14,6,104,NULL,NULL,NULL,1.000,46250.00,0.00,1,46250.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(33,1,'salida_remision',1.000,1859000.00,NULL,NULL,4.000,'remision',6,NULL,NULL,12,NULL,4,'2026-03-22 10:30:00',NOW(),NOW()),
(83,1,'salida_remision',2.000,194000.00,NULL,NULL,13.000,'remision',6,NULL,NULL,13,NULL,4,'2026-03-22 10:30:01',NOW(),NOW()),
(104,1,'salida_remision',1.000,37000.00,NULL,NULL,29.000,'remision',6,NULL,NULL,14,NULL,4,'2026-03-22 10:30:02',NOW(),NOW());

-- REM-00007 (TechOriente S.A.S. – 2026-03-25)
-- prod 70: 2u × 1.336.250=2.672.500 | prod 71: 1u × 1.398.750 → Total: 4.071.250 – PAGADO
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(7,'REM-00007','confirmada','2026-03-25 11:00:00',15,1,4,NULL,'2026-03-25 10:30:00',4071250.00,0.00,0.00,4071250.00,4071250.00,0.00,NULL,0.00,'pagado','2026-04-24',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(15,7,70,NULL,NULL,NULL,2.000,1336250.00,0.00,1,2672500.00,NOW(),NOW()),
(16,7,71,NULL,NULL,NULL,1.000,1398750.00,0.00,1,1398750.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(70,1,'salida_remision',2.000,1069000.00,NULL,NULL,3.000,'remision',7,NULL,NULL,15,NULL,4,'2026-03-25 11:00:00',NOW(),NOW()),
(71,1,'salida_remision',1.000,1119000.00,NULL,NULL,4.000,'remision',7,NULL,NULL,16,NULL,4,'2026-03-25 11:00:01',NOW(),NOW());

-- REM-00008 (Comunicaciones y Sistemas SAS – 2026-03-27)
-- prod 52: 5u × 361.250=1.806.250 | prod 53: 3u × 411.250=1.233.750 → Total: 3.040.000 – PARCIAL (pago 1.500.000, saldo 1.540.000)
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(8,'REM-00008','confirmada','2026-03-27 11:00:00',16,1,4,NULL,'2026-03-27 10:30:00',3040000.00,0.00,0.00,3040000.00,3040000.00,0.00,NULL,1540000.00,'parcial','2026-04-26',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(17,8,52,NULL,NULL,NULL,5.000,361250.00,0.00,1,1806250.00,NOW(),NOW()),
(18,8,53,NULL,NULL,NULL,3.000,411250.00,0.00,1,1233750.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(52,1,'salida_remision',5.000,289000.00,NULL,NULL,10.000,'remision',8,NULL,NULL,17,NULL,4,'2026-03-27 11:00:00',NOW(),NOW()),
(53,1,'salida_remision',3.000,329000.00,NULL,NULL,12.000,'remision',8,NULL,NULL,18,NULL,4,'2026-03-27 11:00:01',NOW(),NOW());

-- REM-00009 (Karol Yohana Suárez – 2026-03-29)
-- prod 78: 5u × 136.250=681.250 | prod 86: 5u × 131.250=656.250 → Total: 1.337.500 – PAGADO
INSERT IGNORE INTO remisiones (id,numero,estado,confirmada_en,cliente_id,bodega_id,usuario_id,cotizacion_id,fecha,subtotal,descuento,impuestos,total,total_confirmado,impuestos_confirmados,snapshot_confirmacion,saldo_pendiente,estado_pago,fecha_vencimiento,observaciones,deleted_at,created_at,updated_at) VALUES
(9,'REM-00009','confirmada','2026-03-29 11:00:00',22,1,4,NULL,'2026-03-29 10:30:00',1337500.00,0.00,0.00,1337500.00,1337500.00,0.00,NULL,0.00,'pagado','2026-04-28',NULL,NULL,NOW(),NOW());
INSERT IGNORE INTO detalle_remisiones (id,remision_id,producto_id,lote,fecha_vencimiento,serial,cantidad,precio_unitario,descuento_unitario,impuesto_id,subtotal,created_at,updated_at) VALUES
(19,9,78,NULL,NULL,NULL,5.000,136250.00,0.00,1,681250.00,NOW(),NOW()),
(20,9,86,NULL,NULL,NULL,5.000,131250.00,0.00,1,656250.00,NOW(),NOW());
INSERT IGNORE INTO movimientos_inventario
(producto_id,bodega_id,tipo_movimiento,cantidad,costo_unitario,lote,fecha_vencimiento,stock_resultante,documento_tipo,documento_id,detalle_compra_id,detalle_venta_id,detalle_remision_id,observacion,usuario_id,fecha_movimiento,created_at,updated_at) VALUES
(78,1,'salida_remision',5.000,109000.00,NULL,NULL,10.000,'remision',9,NULL,NULL,19,NULL,4,'2026-03-29 11:00:00',NOW(),NOW()),
(86,1,'salida_remision',5.000,105000.00,NULL,NULL,10.000,'remision',9,NULL,NULL,20,NULL,4,'2026-03-29 11:00:01',NOW(),NOW());

-- =====================================================
-- PAGOS DE CLIENTES (10)
-- =====================================================
INSERT IGNORE INTO pago_clientes (id,numero,cliente_id,forma_pago_id,banco_id,usuario_id,fecha,monto,referencia,observaciones,created_at,updated_at) VALUES
(1,'PAC-00001',11,1,NULL,4,'2026-03-05 11:00:00',2000000.00,NULL,'Pago efectivo parcial REM-00001 Tecnomax',NOW(),NOW()),
(2,'PAC-00002',11,2,1,4,'2026-03-06 09:00:00',3058750.00,'TRF-20260306-001','Transferencia Bancolombia saldo REM-00001 Tecnomax',NOW(),NOW()),
(3,'PAC-00003',14,6,NULL,4,'2026-03-08 11:30:00',1041250.00,NULL,'Pago Nequi REM-00002 PC Mania',NOW(),NOW()),
(4,'PAC-00004',19,1,NULL,4,'2026-03-12 11:00:00',1500000.00,NULL,'Pago efectivo parcial REM-00003 J.C. Rincón',NOW(),NOW()),
(5,'PAC-00005',12,2,1,4,'2026-03-15 11:00:00',2337500.00,'TRF-20260315-002','Transferencia Bancolombia REM-00004 Sistemas y Redes',NOW(),NOW()),
(6,'PAC-00006',13,7,NULL,4,'2026-03-18 11:00:00',2000000.00,NULL,'Pago Daviplata parcial REM-00005 Cibermax',NOW(),NOW()),
(7,'PAC-00007',20,1,NULL,4,'2026-03-22 11:00:00',2855000.00,NULL,'Pago efectivo REM-00006 Sandra Pedraza',NOW(),NOW()),
(8,'PAC-00008',15,2,1,4,'2026-03-25 12:00:00',4071250.00,'TRF-20260325-003','Transferencia Bancolombia REM-00007 TechOriente',NOW(),NOW()),
(9,'PAC-00009',16,6,NULL,4,'2026-03-27 12:00:00',1500000.00,NULL,'Pago Nequi parcial REM-00008 Comunic. y Sistemas',NOW(),NOW()),
(10,'PAC-00010',22,1,NULL,4,'2026-03-29 12:00:00',1337500.00,NULL,'Pago efectivo REM-00009 Karol Suárez',NOW(),NOW());

INSERT IGNORE INTO detalle_pago_clientes (id,pago_cliente_id,documento_tipo,documento_id,monto_aplicado,created_at,updated_at) VALUES
(1,1,'remision',1,2000000.00,NOW(),NOW()),
(2,2,'remision',1,3058750.00,NOW(),NOW()),
(3,3,'remision',2,1041250.00,NOW(),NOW()),
(4,4,'remision',3,1500000.00,NOW(),NOW()),
(5,5,'remision',4,2337500.00,NOW(),NOW()),
(6,6,'remision',5,2000000.00,NOW(),NOW()),
(7,7,'remision',6,2855000.00,NOW(),NOW()),
(8,8,'remision',7,4071250.00,NOW(),NOW()),
(9,9,'remision',8,1500000.00,NOW(),NOW()),
(10,10,'remision',9,1337500.00,NOW(),NOW());

-- =====================================================
-- PAGOS DE PROVEEDORES (4)
-- =====================================================
INSERT IGNORE INTO pago_proveedores (id,numero,proveedor_id,forma_pago_id,banco_id,usuario_id,fecha,monto,referencia,observaciones,created_at,updated_at) VALUES
(1,'PAP-00001',2,1,NULL,1,'2026-01-15 12:00:00',3440000.00,NULL,'Pago efectivo total COMP-00001 Ingram Micro',NOW(),NOW()),
(2,'PAP-00002',3,2,2,1,'2026-01-28 10:00:00',4000000.00,'TRF-20260128-001','Transferencia Davivienda abono COMP-00002 Intcomex',NOW(),NOW()),
(3,'PAP-00003',9,2,1,1,'2026-02-20 10:00:00',1500000.00,'TRF-20260220-001','Transferencia Bancolombia abono COMP-00004 Sigma',NOW(),NOW()),
(4,'PAP-00004',4,3,1,1,'2026-02-28 10:00:00',3000000.00,'CHQ-20260228-001','Cheque Bancolombia abono COMP-00003 Computex',NOW(),NOW());
INSERT IGNORE INTO detalle_pago_proveedores (id,pago_proveedor_id,compra_id,monto_aplicado,created_at,updated_at) VALUES
(1,1,1,3440000.00,NOW(),NOW()),
(2,2,2,4000000.00,NOW(),NOW()),
(3,3,4,1500000.00,NOW(),NOW()),
(4,4,3,3000000.00,NOW(),NOW());

-- =====================================================
-- STOCK BODEGAS (estado final)
-- =====================================================
INSERT INTO stock_bodegas (producto_id,bodega_id,cantidad,ubicacion,created_at,updated_at) VALUES
(1,1,15.000,NULL,NOW(),NOW()),(2,1,15.000,NULL,NOW(),NOW()),(3,1,17.000,NULL,NOW(),NOW()),
(4,1,15.000,NULL,NOW(),NOW()),(5,1,15.000,NULL,NOW(),NOW()),(6,1,15.000,NULL,NOW(),NOW()),
(7,1,15.000,NULL,NOW(),NOW()),(8,1,7.000,NULL,NOW(),NOW()),(9,1,15.000,NULL,NOW(),NOW()),
(10,1,13.000,NULL,NOW(),NOW()),(11,1,15.000,NULL,NOW(),NOW()),
(12,1,1.000,NULL,NOW(),NOW()),
(13,1,3.000,NULL,NOW(),NOW()),(14,1,4.000,NULL,NOW(),NOW()),(15,1,3.000,NULL,NOW(),NOW()),
(16,1,4.000,NULL,NOW(),NOW()),(17,1,2.000,NULL,NOW(),NOW()),(18,1,2.000,NULL,NOW(),NOW()),
(19,1,2.000,NULL,NOW(),NOW()),(20,1,2.000,NULL,NOW(),NOW()),(21,1,1.000,NULL,NOW(),NOW()),
(22,1,1.000,NULL,NOW(),NOW()),(23,1,4.000,NULL,NOW(),NOW()),(24,1,1.000,NULL,NOW(),NOW()),
(25,1,1.000,NULL,NOW(),NOW()),(26,1,1.000,NULL,NOW(),NOW()),(27,1,1.000,NULL,NOW(),NOW()),
(28,1,1.000,NULL,NOW(),NOW()),(29,1,1.000,NULL,NOW(),NOW()),(30,1,1.000,NULL,NOW(),NOW()),
(31,1,1.000,NULL,NOW(),NOW()),
(32,1,1.000,NULL,NOW(),NOW()),
(33,1,4.000,NULL,NOW(),NOW()),(34,1,3.000,NULL,NOW(),NOW()),(35,1,1.000,NULL,NOW(),NOW()),
(36,1,4.000,NULL,NOW(),NOW()),(37,1,3.000,NULL,NOW(),NOW()),(38,1,2.000,NULL,NOW(),NOW()),
(39,1,2.000,NULL,NOW(),NOW()),(40,1,2.000,NULL,NOW(),NOW()),
(41,1,2.000,NULL,NOW(),NOW()),(42,1,1.000,NULL,NOW(),NOW()),(43,1,1.000,NULL,NOW(),NOW()),
(44,1,2.000,NULL,NOW(),NOW()),(45,1,1.000,NULL,NOW(),NOW()),(46,1,2.000,NULL,NOW(),NOW()),
(47,1,1.000,NULL,NOW(),NOW()),(48,1,1.000,NULL,NOW(),NOW()),
(49,1,15.000,NULL,NOW(),NOW()),(50,1,15.000,NULL,NOW(),NOW()),(51,1,15.000,NULL,NOW(),NOW()),
(52,1,10.000,NULL,NOW(),NOW()),(53,1,12.000,NULL,NOW(),NOW()),(54,1,4.000,NULL,NOW(),NOW()),
(55,1,5.000,NULL,NOW(),NOW()),(56,1,20.000,NULL,NOW(),NOW()),(57,1,15.000,NULL,NOW(),NOW()),
(58,1,15.000,NULL,NOW(),NOW()),(59,1,25.000,NULL,NOW(),NOW()),(60,1,14.000,NULL,NOW(),NOW()),
(61,1,15.000,NULL,NOW(),NOW()),(62,1,15.000,NULL,NOW(),NOW()),(63,1,15.000,NULL,NOW(),NOW()),
(64,1,5.000,NULL,NOW(),NOW()),(65,1,4.000,NULL,NOW(),NOW()),(66,1,5.000,NULL,NOW(),NOW()),
(67,1,5.000,NULL,NOW(),NOW()),(68,1,5.000,NULL,NOW(),NOW()),(69,1,5.000,NULL,NOW(),NOW()),
(70,1,3.000,NULL,NOW(),NOW()),(71,1,4.000,NULL,NOW(),NOW()),(72,1,5.000,NULL,NOW(),NOW()),
(73,1,5.000,NULL,NOW(),NOW()),(74,1,5.000,NULL,NOW(),NOW()),(75,1,5.000,NULL,NOW(),NOW()),
(76,1,5.000,NULL,NOW(),NOW()),(77,1,5.000,NULL,NOW(),NOW()),
(78,1,10.000,NULL,NOW(),NOW()),(79,1,15.000,NULL,NOW(),NOW()),(80,1,15.000,NULL,NOW(),NOW()),
(81,1,15.000,NULL,NOW(),NOW()),(82,1,15.000,NULL,NOW(),NOW()),(83,1,13.000,NULL,NOW(),NOW()),
(84,1,15.000,NULL,NOW(),NOW()),(85,1,15.000,NULL,NOW(),NOW()),(86,1,10.000,NULL,NOW(),NOW()),
(87,1,15.000,NULL,NOW(),NOW()),
(88,1,30.000,NULL,NOW(),NOW()),(89,1,40.000,NULL,NOW(),NOW()),(90,1,30.000,NULL,NOW(),NOW()),
(91,1,10.000,NULL,NOW(),NOW()),(92,1,20.000,NULL,NOW(),NOW()),(93,1,15.000,NULL,NOW(),NOW()),
(94,1,15.000,NULL,NOW(),NOW()),
(95,1,30.000,NULL,NOW(),NOW()),(96,1,30.000,NULL,NOW(),NOW()),(97,1,30.000,NULL,NOW(),NOW()),
(98,1,30.000,NULL,NOW(),NOW()),(99,1,30.000,NULL,NOW(),NOW()),(100,1,15.000,NULL,NOW(),NOW()),
(101,1,30.000,NULL,NOW(),NOW()),(102,1,30.000,NULL,NOW(),NOW()),(103,1,30.000,NULL,NOW(),NOW()),
(104,1,29.000,NULL,NOW(),NOW()),(105,1,30.000,NULL,NOW(),NOW()),(106,1,30.000,NULL,NOW(),NOW()),
(107,1,30.000,NULL,NOW(),NOW()),(108,1,30.000,NULL,NOW(),NOW()),(109,1,30.000,NULL,NOW(),NOW()),
(110,1,30.000,NULL,NOW(),NOW()),(111,1,30.000,NULL,NOW(),NOW()),(112,1,15.000,NULL,NOW(),NOW()),
(113,1,15.000,NULL,NOW(),NOW()),(114,1,30.000,NULL,NOW(),NOW()),(115,1,30.000,NULL,NOW(),NOW()),
(116,1,30.000,NULL,NOW(),NOW()),(117,1,15.000,NULL,NOW(),NOW()),(118,1,15.000,NULL,NOW(),NOW()),
(119,1,15.000,NULL,NOW(),NOW()),
-- BODEGA 2: productos trasladados
(3,2,5.000,NULL,NOW(),NOW()),(8,2,3.000,NULL,NOW(),NOW()),(10,2,2.000,NULL,NOW(),NOW()),
(13,2,2.000,NULL,NOW(),NOW()),(14,2,1.000,NULL,NOW(),NOW()),(16,2,1.000,NULL,NOW(),NOW());

-- =====================================================
-- ACTUALIZAR SALDOS (cartera abierta)
-- =====================================================
UPDATE clientes SET saldo = 2647500.00 WHERE id = 19;   -- Juan Carlos Rincón: REM3 parcial
UPDATE clientes SET saldo = 3485000.00 WHERE id = 13;   -- Cibermax: REM5 parcial
UPDATE clientes SET saldo = 1540000.00 WHERE id = 16;   -- Comunic. y Sistemas: REM8 parcial
UPDATE proveedores SET saldo = 5159840.00 WHERE id = 3; -- Intcomex: COMP2 parcial
UPDATE proveedores SET saldo = 6467820.00 WHERE id = 4; -- Computex: COMP3 parcial
UPDATE proveedores SET saldo = 2595000.00 WHERE id = 9; -- Sigma Sistemas: COMP4 parcial
UPDATE proveedores SET saldo = 2710000.00 WHERE id = 11;-- Distecno: COMP5 pendiente

-- =====================================================
-- FÓRMULAS DE TRANSFORMACIÓN (6 combos)
-- =====================================================
-- Combos simuladores: el producto final YA EXISTE en el catálogo.
--   Los insumos se consumen y se registra 1 unidad del combo en stock.
-- Kits streaming: producto_final_id = NULL (plantilla sin producto destino).
--   Se usan como guía de armado; el destino se asigna al crear la Transformacion.
-- =====================================================
INSERT IGNORE INTO formula_transformaciones
(id, nombre, descripcion, tipo, producto_final_nombre, producto_final_id, cantidad_producto_final, activo, usuario_id, created_at, updated_at) VALUES
(1, 'Combo G920 + DRIVING FORCE (PC-XBOX)',
    'Timón + pedales Logitech G920 con palanca de cambios DRIVING FORCE — compatible PC y Xbox',
    'combo', NULL, 74, 1.000, 1, 1, NOW(), NOW()),
(2, 'Combo G29 + DRIVING FORCE (PC-PLAY)',
    'Timón + pedales Logitech G29 con palanca de cambios DRIVING FORCE — compatible PC y PlayStation',
    'combo', NULL, 75, 1.000, 1, 1, NOW(), NOW()),
(3, 'Combo G923 + DRIVING FORCE (PC-PLAY)',
    'Timón + pedales Logitech G923 con palanca de cambios DRIVING FORCE — compatible PC y PlayStation',
    'combo', NULL, 76, 1.000, 1, 1, NOW(), NOW()),
(4, 'Combo G923 + DRIVING FORCE (PC-XBOX)',
    'Timón + pedales Logitech G923 con palanca de cambios DRIVING FORCE — compatible PC y Xbox',
    'combo', NULL, 77, 1.000, 1, 1, NOW(), NOW()),
(5, 'Kit Streaming Básico',
    'Panel de control Stream Deck Mini + micrófono Wave Neo + cámara Logitech C922 — ideal para inicio en streaming',
    'combo', 'Kit Streaming Básico', NULL, 1.000, 1, 1, NOW(), NOW()),
(6, 'Kit Streaming Pro',
    'Panel Stream Deck XL (32 teclas) + micrófono HyperX QuadCast 2 + cámara 4K Logitech MB Rio — setup profesional',
    'combo', 'Kit Streaming Pro', NULL, 1.000, 1, 1, NOW(), NOW());

INSERT IGNORE INTO formula_transformacion_detalles
(formula_transformacion_id, tipo_linea, producto_id, cantidad, created_at, updated_at) VALUES
-- Combo G920 + DRIVING FORCE (PC-XBOX) → prod74
(1, 'insumo', 70, 1.000, NOW(), NOW()),   -- LOGITECH G920 timón+pedales PC-XBOX
(1, 'insumo', 64, 1.000, NOW(), NOW()),   -- LOGITECH DRIVING FORCE G920 palanca de cambios
-- Combo G29 + DRIVING FORCE (PC-PLAY) → prod75
(2, 'insumo', 71, 1.000, NOW(), NOW()),   -- LOGITECH G29 timón+pedales PC-PLAY
(2, 'insumo', 64, 1.000, NOW(), NOW()),   -- LOGITECH DRIVING FORCE G29 palanca de cambios
-- Combo G923 + DRIVING FORCE (PC-PLAY) → prod76
(3, 'insumo', 72, 1.000, NOW(), NOW()),   -- LOGITECH G923 timón+pedales PC-PLAY
(3, 'insumo', 64, 1.000, NOW(), NOW()),   -- LOGITECH DRIVING FORCE G923 palanca de cambios
-- Combo G923 + DRIVING FORCE (PC-XBOX) → prod77
(4, 'insumo', 73, 1.000, NOW(), NOW()),   -- LOGITECH G923 timón+pedales PC-XBOX
(4, 'insumo', 64, 1.000, NOW(), NOW()),   -- LOGITECH DRIVING FORCE G923 palanca de cambios
-- Kit Streaming Básico → sin producto destino (plantilla)
(5, 'insumo', 52, 1.000, NOW(), NOW()),   -- ELGATO STREAM DECK MINI
(5, 'insumo', 53, 1.000, NOW(), NOW()),   -- ELGATO WAVE NEO micrófono
(5, 'insumo', 51, 1.000, NOW(), NOW()),   -- LOGITECH C922 PRO HD STREAM cámara web
-- Kit Streaming Pro → sin producto destino (plantilla)
(6, 'insumo', 54, 1.000, NOW(), NOW()),   -- ELGATO STREAM DECK XL 32 teclas
(6, 'insumo', 61, 1.000, NOW(), NOW()),   -- HYPERX QUADCAST 2 micrófono
(6, 'insumo', 49, 1.000, NOW(), NOW());   -- LOGITECH MB RIO 4K ULTRA HD cámara web

COMMIT;

-- =====================================================
-- RESUMEN v5 (precios corregidos desde productos.txt)
-- Categorías:  10 | Marcas: 21 | Productos: 119
-- Precio compra: fuente productos.txt (miles × 1000)
-- Precio venta: precio_compra × 1.25 (margen 25%)
-- Tipo mov. inicial: saldo_inicial
-- Proveedores: 5 nuevos (IDs 9-13)
-- Clientes: 15 nuevos (IDs 11-25) Cúcuta
-- Compras: 5 | Ventas: 0 | Traslados: 2 | Remisiones: 9
-- Pagos clientes: 10 | Pagos proveedores: 4
-- Stock bodegas: 125 registros
-- Fórmulas transformación: 6 combos (4 simuladores + 2 streaming)
--   Simuladores: prod74-77 desde componentes prod64+70-73
--   Streaming: Kit Básico (52+53+51) | Kit Pro (54+61+49) — plantillas sin producto final
-- =====================================================
