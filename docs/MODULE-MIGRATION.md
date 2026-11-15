# Business module migration inventory

25 root PHP scripts still execute legacy business code. Shared menu rendering
remains legacy. 13 maintenance routes have Symfony controllers, services and DBAL
repositories. Their legacy scripts were deleted: ERP1000.php, ERP1010.php, ERP1020.php, ERP1030.php, ERP1040.php, ERP1050.php, ERP1060.php, ERP1070.php, ERP1080.php, ERP1090.php, ERP1091.php, ERP1093.php, ERP1094.php.

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
| ERP7010.php | Legacy | ERP7010 | CMtoPagos.php, CMtoPerfiles.php | 26 |
| ERP7020.php | Legacy | ERP7020 | CMtoPagos.php, CMtoPerfiles.php | 26 |
| ERP8000.php | Legacy | ERP8000 | CMtoPedidos.php, CMtoPerfiles.php | 80 |
| ERP9010.php | Legacy | Not declared | CMtoDespacho.php, CBase.php | 14 |
| Mnu0000.php | Legacy | Not declared | CBase.php | 0 |
