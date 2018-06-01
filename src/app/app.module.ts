import { SessionGuard } from './shared/guards/session.guard';
import { AuthService } from './shared/services/auth.service';
import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CollapseModule, BsDropdownModule } from 'ngx-bootstrap';
import { AngularFontAwesomeModule } from 'angular-font-awesome';
import { HttpModule } from '@angular/http';

import { AppComponent } from './app.component';
import { NavbarComponent } from './navbar/navbar.component';
import { HomeComponent } from './home/home.component';
import { routing } from './app.routing';
import { LogonComponent } from './logon/logon.component';
// tslint:disable-next-line:import-blacklist
import 'rxjs';

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    HomeComponent,
    LogonComponent
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
    SessionGuard
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
