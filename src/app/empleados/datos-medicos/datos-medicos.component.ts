import { Component, OnInit } from '@angular/core';
import { environment } from '../../../environments/environment';
import { EmpleadoService } from '../empleado.service';
import { AuthService } from '../../shared/services/auth.service';

@Component({
  selector: 'app-datos-medicos',
  templateUrl: './datos-medicos.component.html',
  styleUrls: ['./datos-medicos.component.scss']
})
export class DatosMedicosComponent implements OnInit {
  public load = false;
  public data: any = {};
  public btnDM = 'Agregar';
  public empleados: any[];
  public selectedEmpleadoId: number;
  // tslint:disable-next-line:no-inferrable-types
  public selectedParienteId: string = '0';
  public carga =  false;
  public parentesco: any[];
  public datosMedicosAll: any[];
  public datosMedicos: any[] = [];
  constructor(
    private empleadoService: EmpleadoService,
    private authService: AuthService,
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
  loadDatosMedicos(parient) {
    this.data = {};
    this.data.dm_tipo = 1;
    this.btnDM = 'Agregar';
    if (environment.debug) { console.log(parient); }
    this.datosMedicos = [];
    this.datosMedicosAll.forEach(
      (dato: any) => {
        if (dato.id_par === parient) {
          this.datosMedicos.push(dato);
        }
      });

      if (environment.debug) { console.log(this.datosMedicos); }
  }
  loadEmpleado(empleado) {
    if (environment.debug) { console.log(empleado); }
    this.carga = false;
    if (empleado > 0) {
      this.load = true;
      this.empleadoService.getDataMedicoEmpleado({index: empleado})
        .then((response: any) => {
          if (environment.debug) { console.log(response); }
          if (response.fun.access) {
            console.log(response.fun.ls);
            this.datosMedicosAll = response.fun.ls.dm;
            this.parentesco   = response.fun.ls.fm;
            this.loadDatosMedicos('0');
            this.load = false;
            this.carga = true;
            console.log(this.data);
            this.data.dm_tipo = 1;
          }
        });
    } else {
      this.data = {};
      this.data.dm_tipo = 1;
      this.btnDM = 'Agregar';
    }
  }
  modificar(id) {
     this.datosMedicos.map((dato: any) => {
       if (id === dato.id) {
        this.data.dm_descripcion = dato.descripcion;
        this.data.dm_tipo = dato.tipo_id;
        this.data.id = dato.id;
        this.btnDM = 'Modificar';
       }
    });
    if (environment.debug) { console.log(this.data); }
  }
  sendDatoMedico() {
    this.data.id_emp = this.selectedEmpleadoId;
    this.data.id_par = this.selectedParienteId;
    if (environment.debug) { console.log(this.data); }
    this.empleadoService.setDatosMedicos(this.data)
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
    });
  }

}
