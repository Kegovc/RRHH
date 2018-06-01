import { Injectable } from '@angular/core';
import { Http } from '@angular/http';
import { environment } from '../../../environments/environment';
// tslint:disable-next-line:import-blacklist
import 'rxjs/Rx';

@Injectable()
export class AuthService {

  constructor(
    private http: Http
  ) { }

  haveAccess() {
    const data = {
      url: window.location.pathname
    };
   return this.http.post(`${environment.api}guard_session`, data)
    .map(response => response.json())
    .toPromise();
  }
}
