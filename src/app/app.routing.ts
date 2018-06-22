import { CatalogosComponent } from './catalogos/catalogos.component';
import { HomeComponent } from './home/home.component';
import { RouterModule, Routes } from '@angular/router';
import { SessionGuard } from './shared/guards/session.guard';
import { LogoffComponent } from './logoff/logoff.component';
import { AdministracionComponent } from './empleados/administracion/administracion.component';
import { FamiliaComponent } from './empleados/familia/familia.component';
import { DatosMedicosComponent } from './empleados/datos-medicos/datos-medicos.component';
import { MovimientoSalarialComponent } from './empleados/movimiento-salarial/movimiento-salarial.component';
import { ExpedientesComponent } from './empleados/expedientes/expedientes.component';


const appRoutes: Routes = [
  {path: '', component: HomeComponent, canActivate: [SessionGuard]},
  {path: 'Empleados/Movimiento_Salarial', component: MovimientoSalarialComponent, canActivate: [SessionGuard]},
  {path: 'Empleados/Administracion', component: AdministracionComponent, canActivate: [SessionGuard]},
  {path: 'Empleados/Datos-Medicos', component: DatosMedicosComponent, canActivate: [SessionGuard]},
  {path: 'Empleados/Expediente', component: ExpedientesComponent, canActivate: [SessionGuard]},
  {path: 'Empleados/Familia', component: FamiliaComponent, canActivate: [SessionGuard]},
  {path: 'Catalogos', component: CatalogosComponent, canActivate: [SessionGuard]},
  {path: 'logoff', component: LogoffComponent},
  {path: '**', component: HomeComponent, canActivate: [SessionGuard]}



];

export const routing = RouterModule.forRoot(appRoutes);
