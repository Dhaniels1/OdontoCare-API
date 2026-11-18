using System.Linq.Expressions;

namespace OdontoCare.Application.Features.Consultas.Interfaces;

public interface IAgendaTransaction : IAsyncDisposable { Task CommitAsync(); }
