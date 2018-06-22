import { HttpClient } from '@angular/common/http';
import { ToastrService } from 'ngx-toastr';
import { Injectable } from '@angular/core';
// tslint:disable-next-line:import-blacklist
import 'rxjs';
import { AuthService } from './../shared/services/auth.service';
import { environment } from './../../environments/environment';

@Injectable()
export class EmpleadoService {

  constructor(
    private http: HttpClient,
    private authService: AuthService,
    private toastr: ToastrService
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
  getDataMedicoEmpleado(data: any) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_datos_medicos_empleados`, data)
    .toPromise();
  }
  getCumpleaños() {
    return this.http.post(`${environment.api}get_festejos`, { accessToken: this.authService.getToken()})
    .toPromise();
  }
  setDatosMedicos(data: any) {
    data.accessToken = this.authService.getToken();
    return this.http.post(`${environment.api}set_datos_medicos`, data)
    .toPromise();
  }
  delDatosMedicos(data: any) {
    data.accessToken = this.authService.getToken();
    return this.http.post(`${environment.api}del_datos_medicos`, data)
    .toPromise();
  }
  getFamilia(data: any) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_familia`, data)
    .toPromise();
  }
  setFamilia(data: any) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}set_familia`, data)
    .toPromise();
  }
  getMovimientos(data: any) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_movimientos`, data)
    .toPromise();
  }
  setMovimiento(data: any) {
    data.accessToken = this.authService.getToken();
    return this.http.post(`${environment.api}set_movimiento`, data)
    .toPromise();
  }

  getExpediente(data: any) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_expediente`, data)
    .toPromise();
  }
  setExpediente(data: any) {
    data.accessToken = this.authService.getToken();
    return this.http.post(`${environment.api}set_expediente`, data)
    .toPromise();
  }

  alerteFaltaDatos(mensaje) {
    this.toastr.error( 'Revise que los datos están completos', 'Datos en blanco');
  }
}
