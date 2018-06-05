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
    return this.http.get(`${environment.api}`)
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
    .map(response => {
      console.log(response);
      return response.json();
    })
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
}
