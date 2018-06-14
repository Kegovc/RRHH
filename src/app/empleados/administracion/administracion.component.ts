import { map } from 'rxjs/operators';
import { Component, OnInit, TemplateRef } from '@angular/core';
import { FormBuilder, FormGroup, FormControl, Validators } from '@angular/forms';
import { BsModalRef, BsModalService } from 'ngx-bootstrap';
import { environment } from './../../../environments/environment';
import { AuthService } from './../../shared/services/auth.service';
import { EmpleadoService } from './../empleado.service';


@Component({
  selector: 'app-administracion',
  templateUrl: './administracion.component.html',
  styleUrls: ['./administracion.component.scss']
})
export class AdministracionComponent implements OnInit {
  public proceso = 0;
  public load = true;
  public  modalRef: BsModalRef;
  public data: any = {
    cia:  '',
    nivel:  '',
    numero_emp:  '',
    status_rh:  '',
    pagadora:  '',
    razon_social:  '',
    nombre:  '',
    segundo_nombre:  '',
    paterno:  '',
    materno:  '',
    fingreso:  '',
    fingreso_:  '',
    puesto:  '',
    division:  '',
    departamento:  '',
    lugar_prestacion:  '',
    horario:  '',
    genero:  '',
    fnacimiento:  '',
    fnacimiento_:  '',
    nacionalidad:  '',
    estado_nacimiento:  '',
    ciudad_nacimiento:  '',
    numero_ss:  '',
    numero_infonavit:  '',
    rfc:  '',
    curp:  '',
    tsangre:  '',
    nivel_estudios:  '',
    carrera:  '',
    titulo:  '',
    direccion:  '',
    cruces:  '',
    colonia:  '',
    estado:  '',
    municipio:  '',
    cp:  '',
    personal_email:  '',
    tcasa:  '',
    cell:  '',
    estado_civil:  '',
    emergencias_nombre:  '',
    emergencias_parentesco:  '',
    emergencias_cel:  '',
    emergencias_oficina:  '',
    emergencias_casa:  '',
    banco:  '',
    clabe:  '',
    salario_mensual:  '',
    dia_pago:  '',
    casa_propia:  '',
    medio_transporte:  ''
  };
  public empleados: any[];
  public selectedEmpleadoId: number;
  public index =  0;
  public inputs = [
    {label: 'Empresa', name: 'cia', type: 'id->', values: 'get_empresas', on: '', disable: true},
    {label: 'Nivel', name: 'nivel', type: 'id->', values: '[1,2,3,4]', on: '', disable: true},
    {label: 'No.', name: 'numero_emp', type: 'num', values: '', on: '', disable: false},
    {label: 'STATUS', name: 'status_rh', type: 'id->', values: '["TC","RV","RC"]', on: '', disable: true},
    {label: 'PAGADORA', name: 'pagadora', type: 'text', values: '', on: '', disable: false},
    {label: 'RAZÓN SOCIAL', name: 'razon_social', type: 'text', values: '', on: '', disable: false},
    {label: 'PRIMER NOMBRE', name: 'nombre', type: 'text', values: '', on: '', disable: false},
    {label: 'SEGUNDO NOMBRE', name: 'segundo_nombre', type: 'text', values: '', on: '', disable: false},
    {label: 'APELLIDO PATERNO', name: 'paterno', type: 'text', values: '', on: '', disable: false},
    {label: 'APELLIDO MATERNO', name: 'materno', type: 'text', values: '', on: '', disable: false},
    {label: 'FECHA DE INGRESO A LA COMPAÑÍA', name: 'fingreso_', type: 'date', values: '', on: '', disable: false},
    {label: 'PUESTO', name: 'puesto', type: 'id->', values: 'get_puestos', on: '', disable: true},
    {label: 'AREA', name: 'division', type: 'id->', values: 'get_division', on: '', disable: true},
    {label: 'DEPARTAMENTO', name: 'departamento', type: 'id->', values: 'get_division', on: '', disable: true},
    {label: 'LUGAR DE PRESTACION DE SERVICIOS', name: 'lugar_prestacion', type: 'id->', values: 'get_lugar', on: '', disable: true},
    {label: 'HORARIO MEGA', name: 'horario', type: 'id->', values: 'get_horarios', on: '', disable: true},
    {label: 'GÉNERO', name: 'genero', type: 'id->', values: '["HOMBRE","MUJER"]', on: '', disable: true},
    {label: 'FECHA DE NACIMIENTO', name: 'fnacimiento_', type: 'date', values: '', on: '', disable: false},
    {label: 'NACIONALIDAD', name: 'nacionalidad', type: 'text', values: '', on: '', disable: false},
    // tslint:disable-next-line:max-line-length
    {label: 'ESTADO DE NACIMIENTO', name: 'estado_nacimiento', type: 'estado', values: 'get_estados', on: 'ciudad_nacimiento', disable: true},
    {label: 'CIUDAD DE NACIMIENTO', name: 'ciudad_nacimiento', type: 'local', values: 'get_municipios', on: '', disable: true},
    {label: 'NSS', name: 'numero_ss', type: 'num', values: '', on: '', disable: false},
    {label: 'INFONAVIT', name: 'numero_infonavit', type: 'num', values: '', on: '', disable: false},
    {label: 'RFC', name: 'rfc', type: 'text', values: '', on: '', disable: false},
    {label: 'CURP', name: 'curp', type: 'text', values: '', on: '', disable: false},
    {label: 'TIPO DE SANGRE', name: 'tsangre', type: 'id->', values: 'get_sangre', on: '', disable: true},
    {label: 'NIVEL ACADEMICO', name: 'nivel_estudios', type: 'id->', values: 'get_estudios', on: '', disable: true},
    {label: 'CARRERA', name: 'carrera', type: 'text', values: '', on: '', disable: false},
    // tslint:disable-next-line:max-line-length
    {label: 'TITULO/PASANTE', name: 'titulo', type: 'id->', values: '["TITULO ","PASANTE","EN TRAMITE","NO APLICA"]', on: '', disable: true},
    {label: 'DIRECCIÓN', name: 'direccion', type: 'text', values: '', on: '', disable: false},
    {label: 'CRUCES DE CALLES', name: 'cruces', type: 'text', values: '', on: '', disable: false},
    {label: 'COLONIA', name: 'colonia', type: 'text', values: '', on: '', disable: false},
    {label: 'ESTADO', name: 'estado', type: 'estado', values: 'get_estados', on: 'municipio', disable: true},
    {label: 'MUNICIPIO', name: 'municipio', type: 'local', values: 'get_municipios', on: '', disable: true},
    {label: 'CP', name: 'cp', type: 'text', values: '', on: '', disable: false},
    {label: 'E-MAIL PERSONAL', name: 'personal_email', type: 'email', values: '', on: '', disable: false},
    {label: 'NO.  DE TELEFONO DE CASA', name: 'tcasa', type: 'text', values: '', on: '', disable: false},
    {label: 'NO. DE TELEFONO DE CELULAR', name: 'cell', type: 'text', values: '', on: '', disable: false},
    {label: 'ESTADO CIVIL', name: 'estado_civil', type: 'id->', values: 'get_civil', on: '', disable: true},
    {label: 'EN CASO DE EMERGENCIA LLAMAR A:', name: 'emergencias_nombre', type: 'text', values: '', on: '', disable: false},
    {label: 'PARENTESCO', name: 'emergencias_parentesco', type: 'text', values: '', on: '', disable: false},
    {label: 'TELEFONO DE EMERGENCIA CELULAR', name: 'emergencias_cel', type: 'text', values: '', on: '', disable: false},
    {label: 'TELEFONO DE EMERGENCIA DE OFICINA', name: 'emergencias_oficina', type: 'text', values: '', on: '', disable: false},
    {label: 'TELEFONO DE EMERGENCIA DE CASA', name: 'emergencias_casa', type: 'text', values: '', on: '', disable: false},
    {label: 'BANCO', name: 'banco', type: 'id->', values: 'get_bancos', on: '', disable: true},
    {label: 'CLABE INTERBANCARIA', name: 'clabe', type: 'text', values: '', on: '', disable: false},
    {label: 'SUELDO BASE MENSUAL', name: 'salario_mensual', type: 'text', values: '', on: '', disable: false},
    {label: 'DIA DE PAGO', name: 'dia_pago', type: 'text', values: '', on: '', disable: false},
    {label: 'CASA PROPIA', name: 'casa_propia', type: 'id->', values: '["SI","NO"]', on: '', disable: true},
    // tslint:disable-next-line:max-line-length
    {label: 'MEDIO DE TRANSPORTE', name: 'medio_transporte', type: 'id->', values: '[{"id":"1","descripcion":"PUBLICO"},{"id":"2","descripcion":"PRIVADO"}]', on: '', disable: true},
  ];

  constructor(
    private formBuilder: FormBuilder,
    private empleadoService: EmpleadoService,
    private authService: AuthService,
    private modalService: BsModalService,
  ) {
    this.selectedEmpleadoId = 0;
    this.empleadoService.getEmpleados()
    .then((response: any) => {
      this.proceso++;
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.empleados = response.fun.ls;
        if (environment.debug) { console.log(this.empleados); }
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    if (environment.debug) { console.log(this.inputs); }
    });
    this.inputs = this.inputs.map(input => {
      if (input.values !== '') {
        try {
          const jsonValues = JSON.parse(input.values);
          input.values = jsonValues.map(value => {
            if ( typeof value !== 'object') {
              return {id: value.toString(), descripcion: value.toString()};
            } else {
              return value;
            }
          });
          input.disable = false;
        } catch (error) {
          this.getData(input.values, this.index);
          input.values = '';
        }
      }
      this.index++;
      return input;
    });
    this.data.fingreso_ = new Date();
    this.data.fnacimiento_ = new Date();
  }

  ngOnInit() {

  }


  openModal(template: TemplateRef<any>) {
    if (environment.debug) { console.log(this.selectedEmpleadoId === 0); }
    if (environment.debug) { console.log(!this.selectedEmpleadoId); }
    this.modalRef = this.modalService.show(template);
  }

  confirm(): void {
    this.data.fingreso = this.getFecha(this.data.fingreso_);
    this.data.fnacimiento = this.getFecha(this.data.fnacimiento_);
    if (environment.debug) { console.log(this.data); }
    this.empleadoService.setEmpleado(this.data)
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        window.location.reload();
      }
      this.modalRef.hide();
    });
    if (environment.debug) { console.log(this.data); }
  }

  decline(): void {
    this.modalRef.hide();
  }

  loadEmpleado(empleado) {
    if (environment.debug) { console.log(empleado); }
    if (empleado > 0) {
      this.load = true;
      this.empleadoService.getEmpleado({index: empleado})
        .then((response: any) => {
          if (environment.debug) { console.log(response); }
          if (response.fun.access) {
            if (environment.debug) { console.log(response.fun.extras); }
            // tslint:disable-next-line:forin
            Object.keys(response.fun.ls).forEach((key, index) => {
              if (environment.debug) { console.log(`${key} ${response.fun.ls[key]}`); }
              if (response.fun.extras !== null) {
                if (key in response.fun.extras) {
                  if (environment.debug) {  console.log(response.fun.extras[key]); }
                  // tslint:disable-next-line:no-shadowed-variable
                  const index = this.inputs.indexOf(this.inputs.find((element) => element.name === key));
                  this.inputs[index].disable = false;
                  this.inputs[index].values = response.fun.extras[key];
                }
              }
              this.data[key] = response.fun.ls[key];
            });
            if (environment.debug) { console.log(this.data); }
            let fecha_a = `${this.data.fingreso}`.split('-');
            this.data.fingreso_ = new Date(Number(fecha_a[0]), Number(fecha_a[1]) - 1, Number(fecha_a[2]));
            fecha_a = `${this.data.fnacimiento}`.split('-');
            this.data.fnacimiento_ = new Date(Number(fecha_a[0]), Number(fecha_a[1]) - 1, Number(fecha_a[2]));
            this.load = false;
          }
        });
    } else {
      window.location.reload();
    }
  }

  onChangeLocalidad(hijo, padre) {
    if (environment.debug) { console.log(`${hijo} ${this.data[padre]}`); }
    // tslint:disable-next-line:no-shadowed-variable
    if (environment.debug) { console.log(this.inputs.find((element) => element.name === padre)); }
    // tslint:disable-next-line:no-shadowed-variable
    const index = this.inputs.indexOf(this.inputs.find((element) => element.name === hijo));
    const get = 'get_municipios';
    this.inputs[index].disable = true;
    this.empleadoService.getData(get, this.data[padre])
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
        if (response.fun.access) {
          this.inputs[index].values = response.fun.ls;
          this.inputs[index].disable = false;
        }
    })
    .catch(error => {
      console.log(`${error}  ${get}`);
    });
    if (environment.debug) { console.log(this.inputs[index]); }
  }

  getData(get: string, index: number) {
    switch (get) {
      case 'get_empresas':
      case 'get_puestos':
      case 'get_division':
      case 'get_lugar':
      case 'get_horarios':
      case 'get_sangre':
      case 'get_estudios':
      case 'get_estados':
      case 'get_civil':
      case 'get_bancos':
        this.empleadoService.getData(get)
          .then((response: any) => {
            this.proceso++;
            if (environment.debug) { console.log(response); }
            if (response.fun.access) {
              this.inputs[index].values = response.fun.ls;
              this.inputs[index].disable = false;
            }
            if (this.proceso === 13) {
              this.load = false;
            }
          })
          .catch(error => {
            console.log(`${error}  ${get}`);
          });
      break;
    }
  }
  getFecha(date: Date): string {
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
    // 1994-01-31
  }
}
