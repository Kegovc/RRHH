import { AuthService } from './../shared/services/auth.service';
import { environment } from './../../environments/environment';
import { VacacionesService } from './../shared/services/vacaciones.service';
import { Component, OnInit, AfterContentChecked } from '@angular/core';

@Component({
  selector: 'app-solicitud-vacaciones',
  templateUrl: './solicitud-vacaciones.component.html',
  styleUrls: ['./solicitud-vacaciones.component.scss']
})
export class SolicitudVacacionesComponent implements OnInit, AfterContentChecked {
  public load = false;
  public data: any = {};
  public usr: any = {};
  public rango_;
  public hoy;
  public carga =  false;
  public vence_ = '';
  public corresponde_;
  public tipo = '';
  public del: any = {};
  public al: any = {};

  constructor(
    private vacacionesService: VacacionesService,
    private authService: AuthService
  ) {
    const now = new Date();
    this.load = true;
    this.hoy = `${now.getDate()}/${now.getMonth()}/${now.getFullYear()}`;
    vacacionesService.getInfo({})
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.data = response.fun.ls;
        this.usr  = response.fun.array;
        console.log(this.data);
        this.carga = true;
        this.data.periodo = 1;
        this.data.diasDisfrutados = 0;
        this.data.back1 = 0;
        this.data.back2 = 0;
        this.data.back3 = 0;
        this.rango_ = [new Date(), new Date()];
        this.rango();
        this.vence();
      }
      this.load = false;
    });
  }

  ngOnInit() {
  }
  ngAfterContentChecked() {
    this.rango();
  }
  rango() {
    if (typeof  this.rango_ !== 'undefined') {
      const mes = [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'

      ];
      let date = new Date();
      date =  this.rango_[0];
      this.del.dia = `${date.getDate()}`;
      this.del.mes = `${mes[date.getMonth()]}`;
      this.del.anio = `${date.getFullYear()}`;
      date =  this.rango_[1];
      this.al.dia = `${date.getDate() + 1}`;
      this.al.mes = `${mes[date.getMonth()]}`;
      this.al.anio = `${date.getFullYear()}`;
      const tomorrow = new Date(this.rango_[0]);
      let dias = 0;
      for (; tomorrow <= this.rango_[1];) {
        const mes_ = (tomorrow.getMonth() + 1) > 9 ? (tomorrow.getMonth() + 1) : ('0' + (tomorrow.getMonth() + 1)) ;
        const str_fecha = `${tomorrow.getFullYear()}-${mes_}-${tomorrow.getDate()}`;
        if (tomorrow.getDay() > 0 && tomorrow.getDay() < 6 && (this.data.diasFeriados.indexOf(str_fecha) < 0)) {
          dias++;
        }

        tomorrow.setDate(tomorrow.getDate() + 1);
      }
      this.data.diasSolicitados = dias;
      this.data.diasRestan = this.data.diasRestante - dias;
    }
  }
  vence() {
    const i = this.data.periodos[this.data.periodo];
    const array = i.split(' al ');
    this.vence_ = array[1];

    this.corresponde();
    this.diasRestante();
  }
  corresponde() {
    this.corresponde_ = this.data.dias[this.data.periodo];
    this.tipo = this.data.periodos_name[this.data.periodo];
  }
  diasRestante() {
    this.data.diasRestante = this.corresponde_ - this.data.diasDisfrutados;
  }
  generarSolicitud() {
    const date = [
      new Date(),
      new Date()
    ];
    date[0] = this.rango_[0];
    date[1] = this.rango_[1];
    this.data.rango = [
      `${date[0].getFullYear()}-${date[0].getMonth() + 1}-${date[0].getDate()}`,
      `${date[1].getFullYear()}-${date[1].getMonth() + 1}-${date[1].getDate()}`
    ];
    this.vacacionesService.setSolicitudVacaciones(this.data)
    .then((response: any) => {
      if (environment.debug) { console.log(response.fun); }
      if (response.fun.access) {
        const b = document.createElement('a');
        b.setAttribute('href', `${environment.api}generate_pdf_solicitud_vacaciones
        ?argument=${response.fun.folio}&accessToken=${this.authService.getToken()}`);
        b.setAttribute('target', `_blanck`);
        b.click();
      }
    });
    console.log(this.data);
  }
}
