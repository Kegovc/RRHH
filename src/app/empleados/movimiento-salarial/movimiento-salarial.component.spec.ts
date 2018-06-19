import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MovimientoSalarialComponent } from './movimiento-salarial.component';

describe('MovimientoSalarialComponent', () => {
  let component: MovimientoSalarialComponent;
  let fixture: ComponentFixture<MovimientoSalarialComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MovimientoSalarialComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MovimientoSalarialComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
