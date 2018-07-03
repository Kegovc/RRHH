import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
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
  typeData: any = {
    nombre: '',
    segundo_nombre: '',
    paterno: '',
    materno: '',
    fingreso: '',
    puesto: '',
    division: '',
    fnacimiento: '',
    numero_ss: '',
    rfc: '',
    curp: '',
    documentos_personales: [
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false
    ],
    documentos_internos: [
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false,
      false
    ]
  };
  public empleados: any[] = [];
  public selectedEmpleadoId: number;
  public carga =  false;

  public selectedFile = null;
  @ViewChild('img') img_: ElementRef;
  public old_avatar: any = {};

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
        this.selectedEmpleadoId = 0;
        this.selectedEmpleadoId = 12;
        this.loadEmpleado(12);
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    });
   }

  loadEmpleado(empleado, _carga = false) {
    if (environment.debug) { console.log(empleado); }
    this.data = this.typeData;
    this.carga = _carga;
    if (empleado > 0) {
      this.load = true;
      this.empleadoService.getExpediente({index: empleado})
        .then((response: any) => {
          if (environment.debug) { console.log(response); }
          if (response.fun.access) {
            this.data = response.fun.ls;
            this.load = false;
            this.carga = true;
            this.old_avatar.alt = response.fun.avatar.alt;
            this.old_avatar.src = `${environment.api}${response.fun.avatar.src}`;
          }
        });
    } else {
      this.data = this.typeData;
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

  onFileSelected(event) {
    this.selectedFile = <File>event.target.files[0];
    console.log(this.selectedFile);
    if (event.target.files && event.target.files[0]) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.img_.nativeElement.src = e.target.result;
      };
      reader.readAsDataURL(this.selectedFile);
    } else {
      this.img_.nativeElement.src = `${this.old_avatar.src}`;
      this.img_.nativeElement.alt = `${this.old_avatar.alt}`;
    }
  }

  onUpLoad() {
    const fb = new FormData();
    fb.append('image', this.selectedFile, this.selectedFile.name);
    fb.append('id', `${this.selectedEmpleadoId}`);
    this.empleadoService.setPictureExpediente(fb)
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.img_.nativeElement.src = `${environment.api}${response.fun.ls}`;
        this.selectedFile = null;
      }
    });
  }

  ngOnInit() {
  }

}
