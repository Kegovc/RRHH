import { environment } from './../../../environments/environment';
import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs/Observable';
import { AuthService } from '../services/auth.service';

@Injectable()
export class SessionGuard implements CanActivate {

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}
  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    this.sessionStatus();
    return true;
  }

  sessionStatus() {
    this.authService.haveAccess()
    .then(response => {
      console.log(response);
      if (!response.fun.access) {
        switch (response.fun.execute) {
          case 'toSSO': {
            // window.location.href = environment.sso;
            break;
          }
          case 'logon': {
            // window.location.href = `${environment.sso}close`;
            break;
          }
        }
      }
    });
  }
}
