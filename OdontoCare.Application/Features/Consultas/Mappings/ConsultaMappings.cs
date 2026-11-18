using OdontoCare.Application.Features.Consultas.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Consultas.Mappings;

public static class ConsultaMappings
{
    public static ConsultaDto ToDto(this Consulta c) => new(c.IdConsulta, c.DataConsulta, c.HorarioConsulta,
            c.StatusConsulta, c.Observacoes, c.IdUsuario, c.IdPaciente, c.IdProcedimento);

    public static Consulta ToEntity(this ConsultaCreateDto d) => new()
        {
            DataConsulta = d.DataConsulta, HorarioConsulta = d.HorarioConsulta,
            StatusConsulta = d.StatusConsulta, Observacoes = d.Observacoes,
            IdUsuario = d.IdUsuario, IdPaciente = d.IdPaciente, IdProcedimento = d.IdProcedimento
        };

    public static Consulta ToEntity(this ConsultaUpdateDto d) => new()
        {
            DataConsulta = d.DataConsulta, HorarioConsulta = d.HorarioConsulta,
            StatusConsulta = d.StatusConsulta, Observacoes = d.Observacoes,
            IdUsuario = d.IdUsuario, IdPaciente = d.IdPaciente, IdProcedimento = d.IdProcedimento
        };
}
