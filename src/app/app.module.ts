import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CollapseModule, BsDropdownModule } from 'ngx-bootstrap';
import { AngularFontAwesomeModule } from 'angular-font-awesome';
import { HttpModule } from '@angular/http';
import { HttpClientModule, HttpClientXsrfModule } from '@angular/common/http';
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
@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    HomeComponent,
    LogoffComponent,
    AdministracionComponent,
    ActiveNavItemDirective,
  ],
  imports: [
    BrowserModule,
    CollapseModule.forRoot(),
    BsDropdownModule.forRoot(),
    AngularFontAwesomeModule,
    HttpModule,
    routing,
  ],
  providers: [
    AuthService,
    SessionGuard,
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
