import { environment } from './../../environments/environment';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from './../shared/services/auth.service';
import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ReporteService {

  constructor(
    private http: HttpClient,
    private authService: AuthService,
    private toastr: ToastrService
  ) { }

  getReportes() {
    const data: any = {};
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_reportes`, data)
    .toPromise();
  }

  getReporte(data) {
    data.accessToken =  this.authService.getToken();
    return this.http.post(`${environment.api}get_reporte`, data)
    .toPromise();
  }

  execute(data) {
    switch (data.execute) {
      case 'download': {

        break;
      }
    }

  }

  alerteFaltaDatos(mensaje = 'Revise que los datos están completos') {
    this.toastr.error( mensaje, 'Datos en blanco');
  }
}
