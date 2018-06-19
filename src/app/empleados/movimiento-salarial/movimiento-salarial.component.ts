import { environment } from './../../../environments/environment';
import { AuthService } from './../../shared/services/auth.service';
import { EmpleadoService } from './../empleado.service';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-movimiento-salarial',
  templateUrl: './movimiento-salarial.component.html',
  styleUrls: ['./movimiento-salarial.component.scss']
})
export class MovimientoSalarialComponent implements OnInit {
  public load = false;
  public data: any = {};
  public empleados: any[] = [];
  public selectedEmpleadoId: number;
  public carga =  false;

  loadEmpleado(empleado, _carga = false) {
    if (environment.debug) { console.log(empleado); }
    this.carga = _carga;
    if (empleado > 0) {
      this.load = true;
      this.empleadoService.getFamilia({index: empleado})
        .then((response: any) => {
          if (environment.debug) { console.log(response); }
          if (response.fun.access) {
            this.load = false;
            this.carga = true;
          }
        });
    } else {
      this.data = {};
    }
  }
  constructor(
    private empleadoService: EmpleadoService,
    private authService: AuthService
  ) {
    this.selectedEmpleadoId = 0;
    this.empleadoService.getEmpleados()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.empleados = response.fun.ls;
        if (environment.debug) { console.log(this.empleados); }
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    });
   }

  ngOnInit() {
  }

}
