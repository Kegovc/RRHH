import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { ToastrModule } from 'ngx-toastr';
// tslint:disable-next-line:max-line-length
import { CollapseModule, BsDropdownModule, BsDatepickerModule, ModalModule, ProgressbarModule, TooltipModule, PopoverModule } from 'ngx-bootstrap';
import { AngularFontAwesomeModule } from 'angular-font-awesome';
import { HttpClientModule, HttpClientXsrfModule } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
// tslint:disable-next-line:import-blacklist
import 'rxjs';
import 'utf8';

import { SessionGuard } from './shared/guards/session.guard';
import { AuthService } from './shared/services/auth.service';
import { AppComponent } from './app.component';
import { NavbarComponent } from './navbar/navbar.component';
import { HomeComponent } from './home/home.component';
import { routing } from './app.routing';
import { LogoffComponent } from './logoff/logoff.component';
import { AdministracionComponent } from './empleados/administracion/administracion.component';
import { ActiveNavItemDirective } from './shared/directives/active-nav-item.directive';
import { MyNumberOnlyDirective } from './shared/directives/my-number-only.directive';
import { DoDisbledDirective } from './shared/directives/do-disbled.directive';
import { DatosMedicosComponent } from './empleados/datos-medicos/datos-medicos.component';
import { FamiliaComponent } from './empleados/familia/familia.component';
import { EmpleadoService } from './empleados/empleado.service';
import { MovimientoSalarialComponent } from './empleados/movimiento-salarial/movimiento-salarial.component';
import { ExpedientesComponent } from './empleados/expedientes/expedientes.component';
import { CatalogosComponent } from './catalogos/catalogos.component';
import { CatalogoService } from './catalogos/catalogo.service';
import { ReportesComponent } from './reportes/reportes.component';
import { ReporteService } from './reportes/reporte.service';







@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    HomeComponent,
    LogoffComponent,
    AdministracionComponent,
    ActiveNavItemDirective,
    MyNumberOnlyDirective,
    DoDisbledDirective,
    DatosMedicosComponent,
    FamiliaComponent,
    MovimientoSalarialComponent,
    CatalogosComponent,
    ExpedientesComponent,
    ReportesComponent,
  ],
  imports: [
    BrowserModule,
    ProgressbarModule.forRoot(),
    CollapseModule.forRoot(),
    BsDropdownModule.forRoot(),
    BsDatepickerModule.forRoot(),
    ModalModule.forRoot(),
    TooltipModule.forRoot(),
    PopoverModule.forRoot(),
    AngularFontAwesomeModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    routing,
    BrowserAnimationsModule, // required animations module
    ToastrModule.forRoot(), // ToastrModule added
  ],
  providers: [
    AuthService,
    SessionGuard,
    EmpleadoService,
    CatalogoService,
    ReporteService
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
