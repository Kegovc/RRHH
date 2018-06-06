import { environment } from './../../../environments/environment';
import { Injectable } from '@angular/core';
import { Http, Headers} from '@angular/http';
// tslint:disable-next-line:import-blacklist
import 'rxjs/Rx';

@Injectable()
export class AuthService {

  constructor(
    private http: Http
  ) { }

  attackSet() {
    return this.http.get(`${environment.api}attack_set`)
    .map(response => response.json())
    .toPromise();
  }
  getProfile() {
    const data = {
      accessToken: this.getToken()
    };
    const url = `${environment.api}get_profile`;
    return this.http.post(url , data)
    .map(response => response.json())
    .toPromise();
  }
  haveAccess() {
    const data = {
      url: window.location.pathname,
      accessToken: this.getToken()
    };
    const url = `${environment.api}guard_session`;
    return this.http.post(url , data)
    .map(response => response.json())
    .toPromise();
  }
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
    switch (execute) {
      case 'toSSO': {
        // window.location.href = environment.sso;
        break;
      }
      case 'logoff': {
        this.clearToken();
        // window.location.href = `${environment.sso}close`;
        break;
      }
    }
  }
}
