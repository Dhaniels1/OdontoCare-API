using System.Linq.Expressions;

namespace OdontoCare.Application.Features.Consultas.Interfaces;

public sealed record HorarioClinica(TimeSpan Inicio, TimeSpan Fim);
