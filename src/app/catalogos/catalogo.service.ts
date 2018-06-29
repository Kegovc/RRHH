import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { ToastrService } from 'ngx-toastr';
// tslint:disable-next-line:import-blacklist
import 'rxjs';
import { AuthService } from './../shared/services/auth.service';
import { environment } from './../../environments/environment';


@Injectable()
export class CatalogoService {

  constructor(
    private http: HttpClient,
    private authService: AuthService,
    private toastr: ToastrService
  ) { }

  getCatalogos() {
    const data: any = {};
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_catalogos`, data)
    .toPromise();
  }

  getCatalogo(id) {
    const data: any = {};
    data.accessToken =  this.authService.getToken();
    data.id = id;
    return this.http.post(`${environment.api}get_catalogo`, data)
    .toPromise();
  }

  setCatalogo(data) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}set_catalogo`, data)
    .toPromise();
  }

  alerteFaltaDatos(mensaje = 'Revise que los datos están completos') {
    this.toastr.error( mensaje, 'Datos en blanco');
  }
}
