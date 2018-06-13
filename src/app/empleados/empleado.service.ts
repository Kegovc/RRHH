import { HttpClient } from '@angular/common/http';
import { AuthService } from './../shared/services/auth.service';
import { environment } from './../../environments/environment';
import { Injectable } from '@angular/core';
// tslint:disable-next-line:import-blacklist
import 'rxjs';

@Injectable()
export class EmpleadoService {

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) { }
  setEmpleado(data: any) {
    data.accessToken = this.authService.getToken();
    return this.http.post(`${environment.api}set_empleado`, data)
    .toPromise();
  }
  getEmpleados() {
    return this.http.post(`${environment.api}get_empleados`, { accessToken: this.authService.getToken()})
    .toPromise();
  }
  getEmpleado(data: any) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_empleado`, data)
    .toPromise();
  }
  getData(get: string, index: any = null) {
    return this.http.post(`${environment.api}${get}`, { index: index, accessToken: this.authService.getToken()})
    .toPromise();
  }
}
