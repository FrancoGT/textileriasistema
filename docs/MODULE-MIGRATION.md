# Business module migration inventory

133 root PHP scripts are present. 13 maintenance routes have new
controllers, services and DBAL repositories. The remaining scripts still execute
legacy business code. Authentication and shared menu rendering remain legacy.

This inventory counts SQL calls in directly included classes. A class can serve
several screens, so counts overlap and are not migration estimates. Permission
names are read from active calls in the entry script, not from database grants.
"Not declared" requires an access review before migrating that route.

Regenerate with node bin/migration-inventory.cjs.

| Script | State | Existing permission | Direct business classes | SQL calls in classes |
| --- | --- | --- | --- | ---: |
| CMtoPagos.php | Legacy | Not declared | CBase.php | 0 |
| CMtoPedidos.php | Legacy | Not declared | CBase.php | 0 |
| CMtoProductos.php | Legacy | Not declared | CBase.php | 0 |
| ERP1000.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1000 | CMtoEmpleados.php, CMtoPerfiles.php | 56 |
| ERP1010.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1010 | CMtoUsuarios.php, CMtoPerfiles.php | 25 |
| ERP1020.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1020 | CMtoPerfiles.php | 10 |
| ERP1030.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1030 | CMtoClientes.php, CMtoPerfiles.php | 22 |
| ERP1040.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1040 | CMtoMaquinas.php, CMtoPerfiles.php | 17 |
| ERP1050.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1050 | CMtoInsumos.php, CMtoPerfiles.php | 30 |
| ERP1060.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1000 | CMtoProveedores.php, CMtoPerfiles.php | 46 |
| ERP1070.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1070 | CMtoProductos.php, CMtoPerfiles.php | 22 |
| ERP1080.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1080 | CMtoTablas.php, CMtoPerfiles.php | 18 |
| ERP1090.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1090 | CMtoAlmacenes.php, CMtoPerfiles.php | 33 |
| ERP1091.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1091 | CMtoAlmacenes.php, CMtoProductos.php, CMtoPerfiles.php | 45 |
| ERP1093.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1093 | CMtoBancos.php, CMtoPerfiles.php | 36 |
| ERP1094.php | Symfony controller and DBAL repositories, pending runtime validation | ERP1094 | CMtoBancos.php, CMtoPerfiles.php | 36 |
| ERP2010.php | Legacy | ERP2010 | CMtoPedidos.php, CDocRTF.php, CMtoPerfiles.php | 87 |
| ERP2020.php | Legacy | ERP2020 | CMtoPedidos.php, CDocRTF.php, CMtoPerfiles.php | 87 |
| ERP3000.php | Legacy | ERP3000 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP3010.php | Legacy | ERP3010 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP3020.php | Legacy | ERP3020 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4000.php | Legacy | ERP4000 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4010.php | Legacy | ERP4010 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4020.php | Legacy | ERP4020 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4030.php | Legacy | ERP4030 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4040.php | Legacy | ERP4040 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4050.php | Legacy | ERP4030 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4060.php | Legacy | ERP4060 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP4070.php | Legacy | ERP4070 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP5010.php | Legacy | ERP5010 | CMtoLogistica.php, CMtoProductos.php, CMtoPerfiles.php | 25 |
| ERP6010.php | Legacy | Not declared | CMtoCtasXCobrar.php | 59 |
| ERP6020.php | Legacy | Not declared | CMtoCtasXCobrar.php | 59 |
| ERP6030.php | Legacy | Not declared | CMtoCtasXCobrar.php | 59 |
| ERP7000.php | Legacy | Not declared | CMtoDespacho.php | 14 |
| ERP7010.php | Legacy | ERP7010 | CMtoPagos.php, CMtoPerfiles.php | 26 |
| ERP7020.php | Legacy | ERP7020 | CMtoPagos.php, CMtoPerfiles.php | 26 |
| ERP8000.php | Legacy | ERP8000 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP9010.php | Legacy | Not declared | CMtoDespacho.php, CBase.php | 14 |
| Mnu0000.php | Legacy | Not declared | CBase.php | 0 |
| Seg1210.php | Legacy | SEG1210 | CMtoProyectos.php, CMtoPerfiles.php | 18 |
| Seg1220.php | Legacy | SEG1220 | CMtoEmpresas.php, CMtoPerfiles.php | 30 |
| Seg1230.php | Legacy | SEG1230 | CMtoCursos.php, CMtoPerfiles.php | 38 |
| Seg1240.php | Legacy | SEG1240 | CMtoNormas.php, CMtoPerfiles.php | 18 |
| Seg1250.php | Legacy | SEG1250 | CMtoCursos.php, CMtoPerfiles.php | 38 |
| Seg1260.php | Legacy | SEG1260 | CMtoEmpleados.php, CMtoPerfiles.php | 56 |
| Seg1270.php | Legacy | SEG1270 | CMtoEmpleados.php, CMtoPerfiles.php | 56 |
| Seg1280.php | Legacy | SEG1280 | CMtoCursos.php, CMtoPerfiles.php | 38 |
| Seg1290.php | Legacy | SEG1290 | CMtoEmpleados.php, CMtoPerfiles.php | 56 |
| Seg1310.php | Legacy | SEG1310 | CMtoLicencias.php, CMtoPerfiles.php | 51 |
| Seg1320.php | Legacy | SEG1320 | CMtoTablas.php, CMtoPerfiles.php | 18 |
| Seg1330.php | Legacy | SEG1330 | CMtoEmpresas.php, CMtoPerfiles.php | 30 |
| Seg1340.php | Legacy | SEG1340 | CMtoFactores.php, CMtoPerfiles.php | 42 |
| Seg1350.php | Legacy | SEG1350 | CMtoTablas.php, CMtoPerfiles.php | 18 |
| Seg1360.php | Legacy | SEG1360 | CMtoLicencias.php, CMtoPerfiles.php | 51 |
| Seg1390.php | Legacy | SEG1390 | CMtoUsuario.php, CMtoPerfiles.php | 26 |
| Seg1410.php | Legacy | SEG1410 | CMtoEmpleados.php, CMtoPerfiles.php | 56 |
| Seg1420.php | Legacy | SEG1420 | CMtoLicencias.php, CMtoPerfiles.php | 51 |
| Seg1430.php | Legacy | SEG1430 | CMtoUnidad.php, CMtoPerfiles.php | 16 |
| Seg1440.php | Legacy | SEG1440 | CMtoLicencias.php, CMtoPerfiles.php | 51 |
| Seg1450.php | Legacy | SEG1450 | CMtoLicencias.php, CMtoPerfiles.php | 51 |
| Seg1510.php | Legacy | SEG1510 | CIncidentesFerroviarios.php, CMtoPerfiles.php | 32 |
| Seg1510a.php | Legacy | SEG1510 | CIncidentesFerroviarios.php, CMtoPerfiles.php | 32 |
| Seg2110.php | Legacy | SEG2110 | CEvaluacion.php, CMtoPerfiles.php | 62 |
| Seg2120.php | Legacy | SEG2120 | CEvaluacion.php, CMtoPerfiles.php | 62 |
| Seg2130.php | Legacy | SEG2130 | CEvaluacion.php, CMtoPerfiles.php | 62 |
| Seg2140.php | Legacy | SEG2140 | CEvaluacion.php, CMtoPerfiles.php | 62 |
| Seg2210.php | Legacy | SEG2210 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2220.php | Legacy | SEG2220 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2230.php | Legacy | SEG2230 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2240.php | Legacy | SEG2240 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2250.php | Legacy | SEG2250 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2260.php | Legacy | SEG2260 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2270.php | Legacy | SEG2270 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2280.php | Legacy | SEG2280 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2310.php | Legacy | SEG2310 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2320.php | Legacy | SEG2320 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2330.php | Legacy | SEG2330 | CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg2340.php | Legacy | SEG2340 | CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg2350.php | Legacy | SEG2350 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg2410.php | Legacy | SEG2410 | CBase.php, CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg2420.php | Legacy | SEG2420 | CBase.php, CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg2510.php | Legacy | SEG2510 | CIncidentes.rep.php, CMtoPerfiles.php | 67 |
| Seg2520.php | Legacy | SEG2520 | CIncidentes.rep.php, CMtoPerfiles.php | 67 |
| Seg2530.php | Legacy | SEG2530 | CPruebas.rep.php, CMtoPerfiles.php | 61 |
| Seg2540.php | Legacy | SEG2540 | CIncidentes.rep.php, CMtoPerfiles.php | 67 |
| Seg2610.php | Legacy | SEG2610 | CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg2620.php | Legacy | SEG2620 | CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg2630.php | Legacy | SEG2630 | CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg2640.php | Legacy | SEG2640 | CConsultasXls.php, CMtoPerfiles.php | 84 |
| Seg4110.php | Legacy | SEG4110 | CReportes.php, CMtoPerfiles.php, CConsultasXls.php | 111 |
| Seg4120.php | Legacy | SEG4120 | CConsultasXls.php, CReportes.php, CMtoPerfiles.php | 111 |
| Seg4130.php | Legacy | SEG4130 | CReportes.php, CMtoPerfiles.php, CConsultasXls.php | 111 |
| Seg4140.php | Legacy | SEG4140 | CReportes.php, CMtoPerfiles.php, CConsultasXls.php | 111 |
| Seg4150.php | Legacy | SEG4150 | CReportes.php, CMtoPerfiles.php, CConsultasXls.php | 111 |
| Seg4160.php | Legacy | SEG4160 | CReportes.php, CMtoPerfiles.php, CConsultasXls.php | 111 |
| Seg4170.php | Legacy | SEG4170 | CConsultas.php, CMtoPerfiles.php | 22 |
| Seg4180.php | Legacy | SEG4180 | CConsultas.php, CMtoPerfiles.php | 22 |
| Seg4190.php | Legacy | SEG4190 | CIncidentes.php, CMtoPerfiles.php | 134 |
| Seg5110.php | Legacy | Not declared | CServicios.php, CMtoPerfiles.php | 15 |
| Seg5110_1.php | Legacy | Not declared | CServicios.php | 5 |
| Seg5120.php | Legacy | SEG5120 | CCursos.rep.php, CMtoPerfiles.php, CConsultasXls.php | 93 |
| Seg5130.php | Legacy | SEG5130 | CCursos.rep.php, CMtoPerfiles.php, CConsultasXls.php | 93 |
| Seg5140.php | Legacy | SEG5140 | CCursos.rep.php, CMtoPerfiles.php | 19 |
| Seg5150.php | Legacy | SEG5150 | CSanciones.rep.php, CMtoPerfiles.php | 19 |
| Seg5160.php | Legacy | SEG5160 | CLicencias.rep.php, CMtoPerfiles.php | 28 |
| Seg5170.php | Legacy | SEG5170 | CLicencias.rep.php, CMtoPerfiles.php | 28 |
| Seg5180.php | Legacy | SEG5180 | CLicencias.rep.php, CMtoPerfiles.php | 28 |
| Seg5190.php | Legacy | SEG5190 | CLicencias.rep.php, CMtoPerfiles.php | 28 |
| Seg5210.php | Legacy | SEG5210 | CPruebas.rep.php, CMtoPerfiles.php | 61 |
| Seg5220.php | Legacy | SEG5220 | CPruebas.rep.php, CMtoPerfiles.php | 61 |
| Seg5230.php | Legacy | SEG5230 | CIncidentes.rep.php, CMtoPerfiles.php | 67 |
| Seg5240.php | Legacy | SEG5240 | CCursos.rep.php, CMtoPerfiles.php | 19 |
| Seg6120.php | Legacy | SEG6120 | CMtoPerfiles.php | 10 |
| Seg6210.php | Legacy | SEG6210 | CMtoPerfiles.php | 10 |
| Seg6220.php | Legacy | SEG6220 | CProcesos.php, CMtoPerfiles.php | 16 |
| Seg7010.php | Legacy | SEG7010 | CConsultasXls.php, CCharlas.rep.php, CMtoPerfiles.php | 87 |
| Seg7020.php | Legacy | SEG7020 | CConsultasXls.php, CCharlas.rep.php, CMtoPerfiles.php | 87 |
| Seg7030.php | Legacy | SEG7030 | CConsultasXls.php, CCharlas.rep.php, CMtoPerfiles.php | 87 |
| Seg7040.php | Legacy | SEG7040 | CConsultasXls.php, CCharlas.rep.php, CMtoPerfiles.php | 87 |
| Seg8001.php | Legacy | Not declared | CMtoUnidad.php | 6 |
| Seg8002.php | Legacy | Not declared | CBase.php | 0 |
| Seg9001.php | Legacy | Not declared | CMtoProyectos.php | 8 |
| Seg9002.php | Legacy | Not declared | CMtoEmpresas.php | 20 |
| Seg9003.php | Legacy | Not declared | CMtoCursos.php | 28 |
| Seg9004.php | Legacy | Not declared | CMtoNormas.php | 8 |
| Seg9005.php | Legacy | Not declared | CMtoEmpleados.php | 46 |
| Seg9006.php | Legacy | Not declared | CEvaluacion.php | 52 |
| Seg9008.php | Legacy | Not declared | CIncidentes.php | 124 |
| Seg9009.php | Legacy | Not declared | CIncidentesFerroviarios.php | 22 |
| Seg9010.php | Legacy | Not declared | CMtoUnidad.php | 6 |
| Seg9011.php | Legacy | Not declared | CIncidentes.php | 124 |
| Seg9901.php | Legacy | Not declared | CIncidentes.php | 124 |
| index.php | Legacy | Not declared | CBase.php | 0 |
