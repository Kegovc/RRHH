import { environment } from './../../environments/environment';
import { VacacionesService } from './../shared/services/vacaciones.service';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-solicitud-vacaciones',
  templateUrl: './solicitud-vacaciones.component.html',
  styleUrls: ['./solicitud-vacaciones.component.scss']
})
export class SolicitudVacacionesComponent implements OnInit {
  public load = false;
  public data: any = {};
  public usr: any = {};
  public rango_;
  public hoy;
  public carga =  false;
  public vence_ = '';

  constructor(
    private vacacionesService: VacacionesService
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
        this.vence();
      }
      this.load = false;
    });
  }

  ngOnInit() {
  }

  rango() {
    console.log(this.rango_);
  }
  vence() {
    const i = this.data.periodos[this.data.periodo];
    const array = i.split(' al ');
    this.vence_ = array[1];
  }
}
