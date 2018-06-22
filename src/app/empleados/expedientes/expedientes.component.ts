import { Component, OnInit } from '@angular/core';
import { AuthService } from './../../shared/services/auth.service';
import { EmpleadoService } from './../empleado.service';
import { environment } from './../../../environments/environment';

@Component({
  selector: 'app-expedientes',
  templateUrl: './expedientes.component.html',
  styleUrls: ['./expedientes.component.scss']
})
export class ExpedientesComponent implements OnInit {
  public load = false;
  public data: any = {};
  public empleados: any[] = [];
  public selectedEmpleadoId: number;
  public carga =  false;

  constructor(
    private empleadoService: EmpleadoService,
    private authService: AuthService
  ) {
    const now = new Date();
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

  loadEmpleado(empleado, _carga = false) {
    if (environment.debug) { console.log(empleado); }
    this.data = {};
    this.carga = _carga;
    if (empleado > 0) {
      this.load = true;
      this.empleadoService.getExpediente({index: empleado})
        .then((response: any) => {
          if (environment.debug) { console.log(response); }
          if (response.fun.access) {
            this.data = response.fun.ls;
            console.log(this.data);
            this.load = false;
            this.carga = true;
          }
        });
    } else {
      this.data = {};
    }
  }
  guardar() {
    const expediente: any = {
      index: this.selectedEmpleadoId,
      documentos_internos: this.data.documentos_internos,
      documentos_personales: this.data.documentos_personales
    };
    this.empleadoService.setExpediente(expediente)
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.loadEmpleado(this.selectedEmpleadoId, true);
      }
    });
  }

  ngOnInit() {
  }

}
