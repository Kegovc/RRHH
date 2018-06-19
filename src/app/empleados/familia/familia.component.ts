import { Component, OnInit } from '@angular/core';
import { EmpleadoService } from '../empleado.service';
import { AuthService } from '../../shared/services/auth.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-familia',
  templateUrl: './familia.component.html',
  styleUrls: ['./familia.component.scss']
})
export class FamiliaComponent implements OnInit {
  public load = false;
  public data: any = {};
  public empleados: any[] = [];
  public selectedEmpleadoId: number;
  public carga =  false;

  public btnFAM = 'Agregar';
  public familiares: any[] = [];
  public tipo_parientes: any[] = [];
  public tipo_sangre: any[] = [];
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
            this.familiares = response.fun.ls;
            this.tipo_parientes = response.fun.pareintes;
            this.tipo_sangre = response.fun.sangre;
            this.refresh();
            if (environment.debug) { console.log(this.familiares); }
          }
        });
    } else {
      this.data = {};
    }
  }
  tipo() {
    console.log(this.data);
  }
  modificar(id) {
    this.data = this.familiares.find((element) => element.id === id);
    const fecha_a = `${this.data.fnacimiento}`.split('-');
    this.data._fnacimiento = new Date(Number(fecha_a[0]), Number(fecha_a[1]) - 1, Number(fecha_a[2]));
    this.btnFAM = 'Modificar';
  }
  accion() {
    if (
      this.data.nombre !== ''  &&
      this.data.paterno !== '' &&
      this.data.materno !== '' &&
      typeof this.data.nombre  !== 'undefined' &&
      typeof this.data.paterno !== 'undefined' &&
      typeof this.data.materno !== 'undefined') {

      this.data.id_empleado = this.selectedEmpleadoId;
      this.data.fnacimiento = this.getFecha(this.data._fnacimiento);
      console.log(this.data);
      this.empleadoService.setFamilia(this.data)
      .then((response: any) => {
        if (environment.debug) { console.log(response); }
        if (response.fun.access) {
          this.loadEmpleado(this.selectedEmpleadoId, true);
        }
      });
    } else {
      this.empleadoService.alerteFaltaDatos('');
    }
  }
  refresh() {
    this.data = {};
    this.data.tipo_id = '1';
    this.data.tsangre_id = '1';
    this.data._fnacimiento = new Date();
    this.btnFAM = 'Agregar';
  }
  getFecha(date: Date): string {
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
    // 1994-01-31
  }
  ngOnInit() {
  }

}
