import { environment } from './../../../environments/environment';
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {Cookie} from 'ng2-cookies';

@Injectable()
export class AuthService {

  constructor(
    private http: HttpClient
  ) { }
  // WEB API
  attackSet() {
    return this.http.get(`${environment.api}attack_set`)
    .toPromise();
  }

  getProfile() {
    const data = {
      accessToken: this.getToken()
    };
    const url = `${environment.api}get_profile`;
    return this.http.post(url , data)
    .toPromise();
  }
  haveAccess() {
    const data = {
      accessToken: this.getToken()
    }; //  Cookie.get('access_token');
    const url = `${environment.api}guard_session`;
    return this.http.post(url , data)
    .toPromise();
  }

  // LocalStorage
  getToken() {
    return localStorage.getItem('tokenAccess');
  }
  clearToken() {
    localStorage.removeItem('tokenAccess');
  }
  isLoggedIn() {
    return !!this.getToken();
  }
  executeAccess(execute) {
    if (environment.debug) { console.log(execute); }
    switch (execute) {
      case 'toSSO': {
        // window.location.href = environment.sso;
        break;
      }
      case 'logoff': {
        // this.clearToken();
        // window.location.href = `${environment.sso}close`;
        break;
      }
      default:
        break;
    }
  }
}
