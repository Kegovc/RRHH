import { HomeComponent } from './home/home.component';
import { RouterModule, Routes } from '@angular/router';
import { SessionGuard } from './shared/guards/session.guard';
import { LogoffComponent } from './logoff/logoff.component';
import { AdministracionComponent } from './empleados/administracion/administracion.component';

const appRoutes: Routes = [
  {path: '', component: HomeComponent, canActivate: [SessionGuard]},
  {path: 'Empleados/Administracion', component: AdministracionComponent, canActivate: [SessionGuard]},
  {path: 'logoff', component: LogoffComponent},
  {path: '**', component: HomeComponent, canActivate: [SessionGuard]}



];

export const routing = RouterModule.forRoot(appRoutes);
