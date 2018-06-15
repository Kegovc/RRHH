import { environment } from './../../environments/environment';
import {  EmpleadoService } from './../empleados/empleado.service';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {
  public title = 'RRHH';
  public tablas: any[] = [];
  public hoy;

  constructor(
    private empleadoService: EmpleadoService
    ) {
      this.hoy = new Date().getDate();
      this.empleadoService.getCumpleaños()
    .then((response: any) => {
      if (environment.debug) {
        console.log(response.fun);
      }
      if (response.fun.access) {
        this.tablas = response.fun.ls;
        if (environment.debug) { console.log(this.tablas); }
      }
    });
    }

  ngOnInit() {}

}
