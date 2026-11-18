using System.Linq.Expressions;

namespace OdontoCare.Application.Features.Consultas.Interfaces;

public interface IAgendaLock
{
    Task<IAgendaTransaction> IniciarAsync();
    Task BloquearAsync(int pacienteId, int usuarioId);
}
