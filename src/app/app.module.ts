import { EmpleadoService } from './empleados/empleado.service';
import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CollapseModule, BsDropdownModule, BsDatepickerModule, ModalModule, ProgressbarModule } from 'ngx-bootstrap';
import { AngularFontAwesomeModule } from 'angular-font-awesome';
import { HttpClientModule, HttpClientXsrfModule } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
// tslint:disable-next-line:import-blacklist
import 'rxjs';

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
  ],
  imports: [
    BrowserModule,
    ProgressbarModule.forRoot(),
    CollapseModule.forRoot(),
    BsDropdownModule.forRoot(),
    BsDatepickerModule.forRoot(),
    ModalModule.forRoot(),
    AngularFontAwesomeModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    routing,
  ],
  providers: [
    AuthService,
    SessionGuard,
    EmpleadoService,
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
