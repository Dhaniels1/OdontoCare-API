
using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.DTOs;

public class ConsultaCreateDto
{
    [Required(ErrorMessage = "A data da consulta é obrigatória.")]
    public DateTime DataConsulta { get; set; }

    [Required(ErrorMessage = "O horário da consulta é obrigatório.")]
    public TimeSpan HorarioConsulta { get; set; }

    [MaxLength(30)]
    public string? StatusConsulta { get; set; }

    public string? Observacoes { get; set; }

    [Range(1, int.MaxValue, ErrorMessage = "Informe um usuário válido.")]
    public int IdUsuario { get; set; }

    [Range(1, int.MaxValue, ErrorMessage = "Informe um paciente válido.")]
    public int IdPaciente { get; set; }

    [Range(1, int.MaxValue, ErrorMessage = "Informe um procedimento válido.")]
    public int IdProcedimento { get; set; }
}
